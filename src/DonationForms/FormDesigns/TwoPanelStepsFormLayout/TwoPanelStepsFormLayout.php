<?php

namespace Give\DonationForms\FormDesigns\TwoPanelStepsFormLayout;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @since 3.6.0
 */
class TwoPanelStepsFormLayout extends FormDesign
{
    protected $isMultiStep = true;

    /**
     * @since 3.6.0
     */
    public static function id(): string
    {
        return 'two-panel-steps';
    }

    /**
     * @since 3.6.0
     */
    public static function name(): string
    {
        return __('Two Panel', 'give');
    }

    /**
     * @since 3.6.0
     */
    public function css(): string
    {
        return GIVE_PLUGIN_URL . 'build/twoPanelStepsFormLayoutCss.css';
    }
}
