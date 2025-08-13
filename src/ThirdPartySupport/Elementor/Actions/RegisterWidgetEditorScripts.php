<?php

namespace Give\ThirdPartySupport\Elementor\Actions;

use Give\DonationForms\AsyncData\Actions\LoadAsyncDataAssets;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @unreleased
 */
class RegisterWidgetEditorScripts
{
    const CAMPAIGN_GOAL_WIDGET_SCRIPT_NAME = 'givewp-elementor-campaign-goal-widget';
    const DONATION_FORM_WIDGET_SCRIPT_NAME = 'givewp-elementor-donation-form-widget';
    const FORM_GRID_WIDGET_SCRIPT_NAME = 'givewp-elementor-donation-form-grid-widget';
    const CAMPAIGN_WIDGET_SCRIPT_NAME = 'givewp-elementor-campaign-widget';
    const CAMPAIGN_GRID_WIDGET_SCRIPT_NAME = 'givewp-elementor-campaign-grid-widget';

    /**
     * @unreleased
     */
    public function __invoke()
    {
        $this->registerDonationFormWidgetScripts();
        $this->registerCampaignGoalWidgetScripts();
        $this->registerFormGridWidgetScripts();
        $this->registerCampaignWidgetScripts();
        $this->registerCampaignGridWidgetScripts();
    }


    /**
     * @unreleased
     */
    private function registerCampaignGoalWidgetScripts()
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorCampaignGoalWidget.asset.php');

        wp_register_script(
            self::CAMPAIGN_GOAL_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorCampaignGoalWidget.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_localize_script(
            self::CAMPAIGN_GOAL_WIDGET_SCRIPT_NAME,
            'GiveCampaignOptions',
            [
                'isAdmin' => false,
                'currency' => give_get_currency(),
            ]
        );

        Language::setScriptTranslations(self::CAMPAIGN_GOAL_WIDGET_SCRIPT_NAME);

        wp_register_style(
            self::CAMPAIGN_GOAL_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/campaignGoalBlockApp.css',
            [],
            $scriptAsset['version']
        );
    }

    /**
     * @unreleased
     */
    private function registerDonationFormWidgetScripts()
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorDonationFormWidget.asset.php');

        wp_register_script(
            self::DONATION_FORM_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorDonationFormWidget.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_register_style(
            self::DONATION_FORM_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorDonationFormWidget.css',
        );
    }

    /**
     * @unreleased
     */
    private function registerFormGridWidgetScripts()
    {
        // this necessary for the form grid widget to display the goal progress bar correctly
        give(LoadAsyncDataAssets::class)->registerAssets();
    }

    /**
     * @unreleased
     */
    private function registerCampaignWidgetScripts()
    {
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorCampaignWidget.asset.php');

        wp_register_script(
            self::CAMPAIGN_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorCampaignWidget.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_register_style(
            self::CAMPAIGN_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorCampaignWidget.css',
            [],
            $asset['version']
        );
    }

    /**
     * @unreleased
     */
    private function registerCampaignGridWidgetScripts()
    {
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorCampaignGridWidget.asset.php');

        wp_register_script(
            self::CAMPAIGN_GRID_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorCampaignGridWidget.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_register_style(
            self::CAMPAIGN_GRID_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorCampaignGridWidget.css',
            [],
            $asset['version']
        );
    }
}
