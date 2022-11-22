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

    /**
     * @unreleased
     */
    public function js(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/classicFormDesignJs.js';
    }

    /**
     * @unreleased
     */
    public function dependencies(): array
    {
        $scriptAsset = require GIVE_NEXT_GEN_DIR . 'build/classicFormDesignJs.asset.php';

        return $scriptAsset['dependencies'];
    }
}
