<?php

namespace Give\DonationForms\FormDesigns\TwoColumnFormDesign;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @unreleased
 */
class TwoColumnFormDesign extends FormDesign
{
    protected $isMultiStep = true;

    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'two-column';
    }

    /**
     * @unreleased
     */
    public static function name(): string
    {
        return __('Two column', 'give');
    }

    /**
     * @unreleased
     */
    public function css(): string
    {
        return GIVE_PLUGIN_URL . 'build/twoColumnFormDesignCss.css';
    }
}
