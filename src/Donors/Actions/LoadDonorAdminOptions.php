<?php

namespace Give\Donors\Actions;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Helpers\IntlTelInput;

/**
 * The purpose of this action is to have a centralized place for localizing options used on many different places
 * by donor scripts (list tables, blocks, etc.)
 *
 * @since 4.6.1 Rename to LoadDonorAdminOptions
 * @since 4.4.0
 */
class LoadDonorAdminOptions
{
    public function __invoke()
    {
        wp_register_script('give-donor-options', false);
        wp_localize_script('give-donor-options', 'GiveDonorOptions', $this->getDonorOptions());
        wp_enqueue_script('give-donor-options');
    }

    /**
     * Get all donor options for localization
     *
     * @return array
     * @since 4.4.0
     */
    private function getDonorOptions(): array
    {
        $isAdmin = is_admin();

        return [
            'isAdmin' => $isAdmin,
            'adminUrl' => admin_url(),
            'apiRoot' => rest_url(DonorRoute::NAMESPACE . '/' . DonorRoute::BASE),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'donorsAdminUrl' => admin_url('edit.php?post_type=give_forms&page=give-donors'),
            'currency' => give_get_currency(),
            'intlTelInputSettings' => IntlTelInput::getSettings(),
            'nameTitlePrefixes' => give_get_option('title_prefixes', array_values(give_get_default_title_prefixes())),
            'countries' => $this->decodeHtmlEntities(give_get_country_list()),
            'states' => $this->getStatesData(),
            'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION') ? GIVE_RECURRING_VERSION : null,
            'mode' => give_is_test_mode() ? 'test' : 'live'
        ];
    }

    /**
     * Get states data with decoded HTML entities
     *
     * @since 4.4.0
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
     * @since 4.4.0
     */
    private function decodeHtmlEntities(array $data, bool $isNested = false): array
    {
        if ($isNested) {
            return array_map(function ($nestedData) {
                return $this->decodeHtmlEntities($nestedData);
            }, $data);
        }

        return array_map(function ($item) {
            return html_entity_decode($item, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
        }, $data);
    }
}
