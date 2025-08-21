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

/**
 * @since 4.7.0
 */
class UnregisterV1Widgets
{
    /**
     * @since 4.7.0
     */
    public function __invoke($widgets_manager)
    {
        $v1Widgets = [
            DonationHistoryWidget::class,
            DonationReceiptWidget::class,
            GiveDonorWallWidget::class,
            GiveFormGridWidget::class,
            GiveFormWidget::class,
            GiveGoalWidget::class,
            GiveLoginWidget::class,
            GiveMultiFormGoalWidget::class,
            GiveProfileEditorWidget::class,
            GiveRegisterWidget::class,
            GiveSubscriptionsWidget::class,
            GiveTotalsWidget::class,
        ];

        foreach ($v1Widgets as $widgetClass) {
            $widget = new $widgetClass();
            $widgets_manager->unregister($widget->get_name());
        }
    }
}
