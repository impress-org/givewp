<?php

namespace Give\Donors\Actions;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Helpers\IntlTelInput;

/**
 * The purpose of this action is to have a centralized place for localizing options used on many different places
 * by donor scripts (list tables, blocks, etc.)
 *
 * @unreleased
 */
class LoadDonorOptions
{
    public function __invoke()
    {
        wp_register_script('give-donor-options', false);

        wp_localize_script('give-donor-options', 'GiveDonorOptions',
            [
                'isAdmin' => is_admin(),
                'adminUrl' => admin_url(),
                'apiRoot' => rest_url(DonorRoute::NAMESPACE . '/' . DonorRoute::BASE),
                'apiNonce' => wp_create_nonce('wp_rest'),
                'donorsAdminUrl' => admin_url('edit.php?post_type=give_forms&page=give-donors'),
                'currency' => give_get_currency(),
                'currencySymbol' => give_currency_symbol(),
                'intlTelInputSettings' => IntlTelInput::getSettings(),
                'nameTitlePrefixes' => give_get_option('title_prefixes', array_values(give_get_default_title_prefixes())),
                'countries' => give_get_country_list(),
                'states' => [
                    'list' => give_states_list(),
                    'labels' => give_get_states_label(),
                    'noStatesCountries' => array_keys(give_no_states_country_list()),
                    'statesNotRequiredCountries' => array_keys(give_states_not_required_country_list()),
                ],
                'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION')
                    ? GIVE_RECURRING_VERSION
                    : null,
                'admin' => is_admin()
                    ? [
                    ]
                    : null,
            ]
        );

        wp_enqueue_script('give-donor-options');
    }
}
