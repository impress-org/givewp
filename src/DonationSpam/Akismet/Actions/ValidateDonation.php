<?php

namespace Give\DonationSpam\Akismet\Actions;

use Give\DonationSpam\Akismet\API;
use Give\DonationSpam\Akismet\DataTransferObjects\CommentCheckArgs;
use Give\DonationSpam\Akismet\DataTransferObjects\SpamContext;
use Give\DonationSpam\EmailAddressWhiteList;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Log\Log;

/**
 * @since 3.15.0
 */
class ValidateDonation
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
     * @since 3.15.0
     */
    public function __construct(API $akismet, EmailAddressWhiteList $whitelist)
    {
        $this->akismet = $akismet;
        $this->whitelist = $whitelist;
    }

    /**
     * @unreleased replaced params to $email, $comment, $firstName, $lastName
     * @since 3.15.0
     *
     * @param  string  $email
     * @param  string  $comment
     * @param  string  $firstName
     * @param  string|null  $lastName
     * @throws SpamDonationException
     */
    public function __invoke(string $email, string $comment, string $firstName, string $lastName): void
    {
        if(!$this->whitelist->validate($email)) {

            $args = CommentCheckArgs::make($comment, $email, $firstName);
            $response = $this->akismet->commentCheck($args);
            $spam = 'true' === $response[1];

            if($spam) {
                $message = "This donor's email ($firstName $lastName - $email) has been flagged as SPAM";
                if(!give_akismet_is_email_logged($email)) {
                    Log::spam($message, (array) new SpamContext($args, $response));
                }
                throw new SpamDonationException($message);
            }
        }
    }
}
