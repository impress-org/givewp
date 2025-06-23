<?php

namespace Give\Donations\Actions;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\BetaFeatures\Facades\FeatureFlag;
use Give\Helpers\IntlTelInput;

/**
 * The purpose of this action is to have a centralized place for localizing options used on many different places
 * by donation scripts (list tables, blocks, etc.)
 *
 * @unreleased
 */
class LoadDonationOptions
{
    public function __invoke()
    {
        wp_register_script('give-donation-options', false);
        wp_localize_script('give-donation-options', 'GiveDonationOptions', $this->getDonationOptions());
        wp_enqueue_script('give-donation-options');
    }

    /**
     * Get all donation options for localization
     *
     * @return array
     * @unreleased
     */
    private function getDonationOptions(): array
    {
        $isAdmin = is_admin();

        return [
            'isAdmin' => $isAdmin,
            'adminUrl' => admin_url(),
            'apiRoot' => rest_url(DonationRoute::NAMESPACE . '/' . DonationRoute::BASE),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'donationsAdminUrl' => admin_url('edit.php?post_type=give_forms&page=give-payment-history'),
            'currency' => give_get_currency(),
            'currencySymbol' => give_currency_symbol(),
            'intlTelInputSettings' => IntlTelInput::getSettings(),
            'nameTitlePrefixes' => give_get_option('title_prefixes', array_values(give_get_default_title_prefixes())),
            'countries' => $this->decodeHtmlEntities(give_get_country_list()),
            'states' => $this->getStatesData(),
            'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION') ? GIVE_RECURRING_VERSION : null,
            'admin' => $isAdmin ? [] : null,
            'eventTicketsEnabled' => FeatureFlag::eventTickets(),
            'isFeeRecoveryEnabled' => defined('GIVE_FEE_RECOVERY_VERSION'),
        ];
    }

    /**
     * Get states data with decoded HTML entities
     *
     * @unreleased
     */
    private function getStatesData(): array
    {
        return [
            'list' => $this->decodeHtmlEntities(give_states_list(), true),
            'labels' => give_get_states_label(),
            'noStatesCountries' => array_keys(give_no_states_country_list()),
            'statesNotRequiredCountries' => array_keys(give_states_not_required_country_list()),
        ];
    }

    /**
     * Decode HTML entities from an array of strings or nested arrays
     *
     * @unreleased
     */
    private function decodeHtmlEntities(array $data, bool $isNested = false): array
    {
        if ($isNested) {
            return array_map(function($nestedData) {
                return $this->decodeHtmlEntities($nestedData);
            }, $data);
        }

        return array_map(function($item) {
            return html_entity_decode($item, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
        }, $data);
    }
}
