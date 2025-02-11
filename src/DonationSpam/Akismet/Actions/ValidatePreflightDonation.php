<?php

namespace Give\DonationSpam\Akismet\Actions;

use Give\DonationSpam\Akismet\API;
use Give\DonationSpam\Akismet\DataTransferObjects\CommentCheckArgs;
use Give\DonationSpam\Akismet\DataTransferObjects\SpamContext;
use Give\DonationSpam\EmailAddressWhiteList;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Log\Log;

/**
 * This is used to validate the data during the preflight validation request.
 *
 * @unreleased
 */
class ValidatePreflightDonation
{
    /**
     * @var API
     */
    protected $akismet;

    /**
     * @var EmailAddressWhiteList
     */
    protected $whitelist;

    /**
     * @unreleased
     */
    public function __construct(API $akismet, EmailAddressWhiteList $whitelist)
    {
        $this->akismet = $akismet;
        $this->whitelist = $whitelist;
    }

    /**
     * @unreleased
     *
     * @param  array  $data
     *
     * @throws SpamDonationException
     */
    public function __invoke(array $data): void
    {
        $comment = $data['comment'] ?? '';
        $email = $data['email'] ?? '';
        $firstName = $data['firstName'] ?? '';
        $lastName = $data['lastName'] ?? '';

        if (!$this->whitelist->validate($email)) {
            $args = CommentCheckArgs::make($comment, $email, $firstName);

            $response = $this->akismet->commentCheck($args);
            $spam = 'true' === $response[1];

            if ($spam) {
                $message = "This donor's email ($firstName $lastName - $email) has been flagged as SPAM";
                if (!give_akismet_is_email_logged($email)) {
                    Log::spam($message, (array)new SpamContext($args, $response));
                }
                throw new SpamDonationException($message);
            }
        }
    }
}
