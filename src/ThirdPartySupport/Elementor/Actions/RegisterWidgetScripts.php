<?php

namespace Give\ThirdPartySupport\Elementor\Actions;

use Give\DonationForms\AsyncData\Actions\LoadAsyncDataAssets;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

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
        $this->registerDonationFormWidgetScripts();
        $this->registerCampaignGoalWidgetScripts();
          // this necessary for the form grid widget to display the goal progress bar
        give(LoadAsyncDataAssets::class)->registerAssets();
    }

    private function registerCampaignGoalWidgetScripts()
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorCampaignGoalWidget.asset.php');
        $scriptName = 'givewp-elementor-campaign-goal-widget';

        wp_register_script('give-campaign-options', false);

        wp_localize_script(
            'give-campaign-options',
            'GiveCampaignOptions',
            [
                'isAdmin' => false,
                'currency' => give_get_currency(),
            ]
        );

        wp_register_script(
            $scriptName,
            GIVE_PLUGIN_URL . 'build/elementorCampaignGoalWidget.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        Language::setScriptTranslations($scriptName);

        wp_enqueue_style(
            $scriptName,
            GIVE_PLUGIN_URL . 'build/campaignGoalBlockApp.css',
            [],
            $scriptAsset['version']
        );
    }
    private function registerDonationFormWidgetScripts()
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorDonationFormWidget.asset.php');
        $scriptName = 'givewp-elementor-donation-form-widget';

        wp_register_script(
            $scriptName,
            GIVE_PLUGIN_URL . 'build/elementorDonationFormWidget.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_style(
            $scriptName,
            GIVE_PLUGIN_URL . 'build/elementorDonationFormWidget.css',
        );
    }
}
