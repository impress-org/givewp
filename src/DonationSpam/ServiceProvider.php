<?php

namespace Give\DonationSpam;

use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\DonationSpam\Akismet\Actions\ValidateDonation;
use Give\DonationSpam\Exceptions\SpamDonationException;
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
                (array) apply_filters( 'give_akismet_whitelist_emails', give_akismet_get_whitelisted_emails() )
            );
        });
    }

    /**
     * @since 3.15.0
     * @inheritDoc
     * @throws SpamDonationException
     */
    public function boot(): void
    {
        if($this->isAkismetEnabledAndConfigured()) {
            add_action('givewp_donation_form_fields_validated', static function(array $data) {
                give(ValidateDonation::class)($data['email'] ?? '', $data['comment'] ?? '', $data['firstName'] ?? '', $data['lastName'] ?? '');
            });
        }
    }

    /**
     * @since 3.15.0
     * @return bool
     */
    public function isAkismetEnabledAndConfigured(): bool
    {
        return
            give_check_akismet_key()
            && give_is_setting_enabled(
                give_get_option( 'akismet_spam_protection', 'enabled')
            );
    }
}
