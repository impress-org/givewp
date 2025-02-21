<?php

namespace Give\Campaigns\Actions;

/**
 * The purpose of this action is to have a centralized place for localizing options used on many different places
 * by campaign scripts (list tables, blocks, etc.)
 *
 * @unreleased
 */
class LoadCampaignOptions
{
    public function __invoke()
    {
        wp_register_script('give-campaign-options', false);

        wp_localize_script('give-campaign-options', 'GiveCampaignOptions',
            [
                'adminUrl' => admin_url(),
                'currency' => give_get_currency(),
                'currencySymbol' => give_currency_symbol(),
                'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION')
                    ? GIVE_RECURRING_VERSION
                    : null,
            ]
        );

        wp_enqueue_script('give-campaign-options');
    }
}
