<?php

namespace Give\DonationSpam;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     * @inheritDoc
     */
    public function register(): void
    {
        give()->singleton(EmailAddressWhiteList::class, function () {
            return new EmailAddressWhiteList(
                apply_filters( 'give_akismet_whitelist_emails', give_akismet_get_whitelisted_emails() )
            );
        });
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        if($this->isAkismetEnabledAndConfigured()) {
            Hooks::addFilter('givewp_donate_form_data_validated', Akismet\Actions\ValidateDonation::class);
        }
    }

    /**
     * @unreleased
     * @return bool
     */
    public function isAkismetEnabledAndConfigured(): bool
    {
        return
            give_is_setting_enabled( give_get_option( 'akismet_spam_protection' ) )
            && give_check_akismet_key();
    }
}
