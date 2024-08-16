<?php

namespace Give\DonationSpam\Akismet\Actions;

use Akismet;
use Give\DonationForms\DataTransferObjects\DonateControllerData;
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
     * @since 3.15.0
     *
     * @param DonateControllerData $data
     *
     * @throws SpamDonationException
     */
    public function __invoke(DonateControllerData $data): void
    {
        if(!$this->whitelist->validate($data->email)) {

            $args = CommentCheckArgs::make($data);
            $response = $this->akismet->commentCheck($args);
            $spam = 'true' === $response[1];

            if($spam) {
                $message = "This donor's email ($data->firstName $data->lastName - $data->email) has been flagged as SPAM";
                if(!give_akismet_is_email_logged($data->email)) {
                    Log::spam($message, (array) new SpamContext($args, $response));
                }
                throw new SpamDonationException($message);
            }
        }
    }
}
