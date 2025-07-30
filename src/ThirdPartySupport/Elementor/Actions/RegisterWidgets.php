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
use Give\ThirdPartySupport\Elementor\Widgets\V2\ElementorCampaignFormWidget\ElementorCampaignFormWidget;

/**
 * @unreleased
 */
class RegisterWidgets
{
    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    private function registerV2Widgets($widgets_manager)
    {
        $widgets_manager->register(new ElementorCampaignFormWidget());
    }
}
