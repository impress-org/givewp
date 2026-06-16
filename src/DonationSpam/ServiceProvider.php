<?php

namespace Give\DonationSpam;

use Give\DonationSpam\Akismet\Actions\ValidateDonationOnFinalSubmission;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 3.15.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 3.15.0
     * @inheritDoc
     */
    public function register(): void
    {
        /**
         * @since 3.15.1 Case filtered value as an array to enforce type.
         * @since 3.15.0
         */
        give()->singleton(EmailAddressWhiteList::class, function () {
            return new EmailAddressWhiteList(
                (array)apply_filters('give_akismet_whitelist_emails', give_akismet_get_whitelisted_emails())
            );
        });
    }

    /**
     * @since TBD register an invokable action that gates the Akismet check to the final submission
     * @since 3.22.0 updated Akismet validation to use new givewp_donation_form_fields_validated action
     * @since 3.15.0
     *
     * @inheritDoc
     */
    public function boot(): void
    {
        Hooks::addAction(
            'givewp_donation_form_fields_validated',
            ValidateDonationOnFinalSubmission::class,
            '__invoke',
            10,
            2
        );
    }
}
