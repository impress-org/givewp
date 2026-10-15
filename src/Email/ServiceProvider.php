<?php

namespace Give\Email;

use Give\Email\Notifications\DonationMicrodepositVerificationEmail;
use Give\Email\Notifications\DonationProcessingReceipt;
use Give\Helpers\Hooks;
use Give_Email_Notification;

/**
 * @since 2.17.1
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider
{

    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addFilter('give_email_notifications', self::class, 'loadEmailNotifications');
        Hooks::addAction('give_add_email_tags', DonationMicrodepositVerificationEmail::class, 'registerEmailTag');
        Hooks::addAction('admin_init', GlobalSettingValidator::class);
    }

    /**
     * @since 2.24.0
     * @since TBD Add the ACH microdeposit verification notification.
     *
     * @param Give_Email_Notification[] $emails
     *
     * @return Give_Email_Notification[]
     */
    public function loadEmailNotifications(array $emails): array
    {
        array_splice($emails, 2, 0, [DonationProcessingReceipt::get_instance()]);
        $emails[] = DonationMicrodepositVerificationEmail::get_instance();

        return $emails;
    }
}
