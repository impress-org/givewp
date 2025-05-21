<?php

namespace Give\Donors\Actions;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;

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
                'apiRoot' => rest_url(DonorRoute::NAMESPACE . '/' . DonorRoute::DONORS),
                'apiNonce' => wp_create_nonce('wp_rest'),
                'donorsAdminUrl' => admin_url('edit.php?post_type=give_forms&page=give-donors'),
                'currency' => give_get_currency(),
                'currencySymbol' => give_currency_symbol(),
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