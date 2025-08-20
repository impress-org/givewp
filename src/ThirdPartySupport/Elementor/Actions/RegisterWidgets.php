<?php

namespace Give\ThirdPartySupport\Elementor\Actions;

use Give\ThirdPartySupport\Elementor\Widgets\V1\DonationHistoryWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\DonationReceiptWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveDonorWallWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveFormGridWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveFormWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveGoalWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveLoginWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveMultiFormGoalWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveProfileEditorWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveRegisterWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveSubscriptionsWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V1\GiveTotalsWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonationsWidget\ElementorCampaignDonationsWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignDonorsWidget\ElementorCampaignDonorsWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGoalWidget\ElementorCampaignGoalWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignStatsWidget\ElementorCampaignStatsWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormWidget\ElementorDonationFormWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonorWallWidget\ElementorDonorWallWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorDonationFormGridWidget\ElementorDonationFormGridWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignGridWidget\ElementorCampaignGridWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignWidget\ElementorCampaignWidget;
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignCommentsWidget\ElementorCampaignCommentsWidget;

/**
 * @since 4.7.0
 */
class RegisterWidgets
{
    /**
     * @since 4.7.0
     */
    public function __invoke($widgets_manager)
    {
        $this->registerV1Widgets($widgets_manager);
        $this->registerV2Widgets($widgets_manager);
    }

    /**
     * Register the v1 widgets that were previously available in the GiveWP Elementor Widgets plugin.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     * @since 4.7.0
     */
    private function registerV1Widgets($widgets_manager)
    {
        if (apply_filters('givewp_elementor_legacy_widgets_enabled', give_is_setting_enabled(give_get_option('givewp_elementor_legacy_widgets_enabled', 'disabled')))) {
            /** @var \Elementor\Widgets_Manager $widgets_manager */
            $widgets_manager->register(new DonationHistoryWidget());
            $widgets_manager->register(new DonationReceiptWidget());
            $widgets_manager->register(new GiveDonorWallWidget());
            $widgets_manager->register(new GiveFormGridWidget());
            $widgets_manager->register(new GiveFormWidget());
            $widgets_manager->register(new GiveGoalWidget());
            $widgets_manager->register(new GiveLoginWidget());
            $widgets_manager->register(new GiveMultiFormGoalWidget());
            $widgets_manager->register(new GiveProfileEditorWidget());
            $widgets_manager->register(new GiveRegisterWidget());
            $widgets_manager->register(new GiveTotalsWidget());

            if (defined('GIVE_RECURRING_VERSION')) {
                $widgets_manager->register(new GiveSubscriptionsWidget());
            }
        }
    }

    /**
     * Register the v2 widgets that are available in GiveWP.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     * @since 4.7.0
     */
    private function registerV2Widgets($widgets_manager)
    {
        $widgets_manager->register(new ElementorCampaignDonationsWidget());
        $widgets_manager->register(new ElementorCampaignDonorsWidget());
        $widgets_manager->register(new ElementorCampaignGoalWidget());
        $widgets_manager->register(new ElementorCampaignStatsWidget());
        $widgets_manager->register(new ElementorDonationFormWidget());
        $widgets_manager->register(new ElementorDonorWallWidget());
        $widgets_manager->register(new ElementorDonationFormGridWidget());
        $widgets_manager->register(new ElementorCampaignGridWidget());
        $widgets_manager->register(new ElementorCampaignWidget());
        $widgets_manager->register(new ElementorCampaignCommentsWidget());
    }
}
