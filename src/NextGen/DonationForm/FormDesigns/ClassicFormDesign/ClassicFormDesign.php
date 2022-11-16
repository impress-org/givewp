<?php

namespace Give\NextGen\DonationForm\FormDesigns\ClassicFormDesign;

use Give\NextGen\Framework\FormDesigns\FormDesign;

/**
 * @unreleased
 */
class ClassicFormDesign extends FormDesign
{
    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'classic';
    }

    /**
     * @unreleased
     */
    public static function name(): string
    {
        return __('Classic', 'give');
    }

    /**
     * @unreleased
     */
    public function css(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/classicFormDesignCss.css';
    }
}
