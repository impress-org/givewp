<?php

namespace Give\DonationForms\FormDesigns\TwoPanelStepsFormLayout;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @unreleased
 */
class TwoPanelStepsFormLayout extends FormDesign
{
    protected $isMultiStep = true;

    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'two-panel-steps';
    }

    /**
     * @unreleased
     */
    public static function name(): string
    {
        return __('Two Panel (Steps)', 'give');
    }

    /**
     * @unreleased
     */
    public function css(): string
    {
        return GIVE_PLUGIN_URL . 'build/twoPanelStepsFormLayoutCss.css';
    }
}
