<?php

namespace Give\ThirdPartySupport\Elementor\Actions;

use Give\DonationForms\AsyncData\Actions\LoadAsyncDataAssets;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class RegisterWidgetScripts
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
            $campaignFormWidgetScriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorCampaignFormWidget.asset.php');
            $campaignFormWidgetScriptName = 'givewp-elementor-campaign-form-widget';

            wp_register_style(
                $campaignFormWidgetScriptName,
                GIVE_PLUGIN_URL . 'build/elementorCampaignFormWidget.css',
            );

            wp_register_script(
                $campaignFormWidgetScriptName,
                GIVE_PLUGIN_URL . 'build/elementorCampaignFormWidget.js',
                array_merge($campaignFormWidgetScriptAsset['dependencies'], ['elementor-frontend']),
                $campaignFormWidgetScriptAsset['version'],
                true
            );

            $donationFormWidgetScriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorDonationFormWidget.asset.php');
            $donationFormWidgetScriptName = 'givewp-elementor-donation-form-widget';

            wp_register_style(
                $donationFormWidgetScriptName,
                GIVE_PLUGIN_URL . 'build/elementorDonationFormWidget.css',
            );

            wp_register_script(
                $donationFormWidgetScriptName,
                GIVE_PLUGIN_URL . 'build/elementorDonationFormWidget.js',
                array_merge($donationFormWidgetScriptAsset['dependencies'], ['elementor-frontend']),
                $donationFormWidgetScriptAsset['version'],
                true
            );

            wp_register_style(
                $donationFormWidgetScriptName,
                GIVE_PLUGIN_URL . 'build/elementorDonationFormWidget.css',
            );

            // this necessary for the form grid widget to display the goal progress bar
            give(LoadAsyncDataAssets::class)->registerAssets();
    }
}
