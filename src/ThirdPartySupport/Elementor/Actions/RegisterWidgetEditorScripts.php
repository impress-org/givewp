<?php

namespace Give\ThirdPartySupport\Elementor\Actions;

use Give\DonationForms\AsyncData\Actions\LoadAsyncDataAssets;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * This class is used to register the scripts for the Elementor widgets in the editor.
 *
 * Some widgets render themselves using javascript which works fine on the frontend but since the editor is in an iframe it doesn't work properly.  So these scripts are mainly used to render the widgets in the editor.
 *
 * @since 4.7.0
 */
class RegisterWidgetEditorScripts
{
    const CAMPAIGN_GOAL_WIDGET_SCRIPT_NAME = 'givewp-elementor-campaign-goal-widget';
    const DONATION_FORM_WIDGET_SCRIPT_NAME = 'givewp-elementor-donation-form-widget';
    const FORM_GRID_WIDGET_SCRIPT_NAME = 'givewp-elementor-donation-form-grid-widget';
    const CAMPAIGN_WIDGET_SCRIPT_NAME = 'givewp-elementor-campaign-widget';
    const CAMPAIGN_GRID_WIDGET_SCRIPT_NAME = 'givewp-elementor-campaign-grid-widget';
    const CAMPAIGN_COMMENTS_WIDGET_SCRIPT_NAME = 'givewp-elementor-campaign-comments-widget';
    const LEGACY_GIVE_FORM_WIDGET_SCRIPT_NAME = 'givewp-elementor-legacy-give-form-widget';

    /**
     * @since 4.7.0
     */
    public function __invoke()
    {
        $this->registerDonationFormWidgetScripts();
        $this->registerCampaignGoalWidgetScripts();
        $this->registerFormGridWidgetScripts();
        $this->registerCampaignWidgetScripts();
        $this->registerCampaignGridWidgetScripts();
        $this->registerCampaignCommentsWidgetScripts();
        $this->registerLegacyGiveFormWidgetScripts();
    }


    /**
     * @since 4.7.0
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
     * @since 4.7.1
     */
    private function registerLegacyGiveFormWidgetScripts()
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorLegacyGiveFormWidget.asset.php');

        wp_register_script(
            self::LEGACY_GIVE_FORM_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorLegacyGiveFormWidget.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_register_style(
            self::LEGACY_GIVE_FORM_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorLegacyGiveFormWidget.css',
            [],
            $scriptAsset['version']
        );
    }

    /**
     * @since 4.7.0
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
     * @since 4.7.0
     */
    private function registerFormGridWidgetScripts()
    {
        // this necessary for the form grid widget to display the goal progress bar correctly
        give(LoadAsyncDataAssets::class)->registerAssets();
    }

    /**
     * @since 4.7.0
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
     * @since 4.7.0
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

    /**
     * @since 4.7.0
     */
    private function registerCampaignCommentsWidgetScripts()
    {
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorCampaignCommentsWidget.asset.php');

        wp_register_script(
            self::CAMPAIGN_COMMENTS_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorCampaignCommentsWidget.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_register_style(
            self::CAMPAIGN_COMMENTS_WIDGET_SCRIPT_NAME,
            GIVE_PLUGIN_URL . 'build/elementorCampaignCommentsWidget.css',
            [],
            $asset['version']
        );
    }
}
