<?php

namespace Give\DonationForms\FormDesigns\ClassicFormDesign;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @since 3.0.0
 */
class ClassicFormDesign extends FormDesign
{
    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'classic';
    }

    /**
     * @since 3.0.0
     */
    public static function name(): string
    {
        return __('Classic', 'give');
    }

    /**
     * @since 3.0.0
     */
    public function css(): string
    {
        return GIVE_PLUGIN_URL . 'build/classicFormDesignCss.css';
    }

    /**
     * @since 3.0.0
     */
    public function js(): string
    {
        return GIVE_PLUGIN_URL . 'build/classicFormDesignJs.js';
    }

    /**
     * @since 3.0.0
     */
    public function dependencies(): array
    {
        $scriptAsset = require GIVE_PLUGIN_DIR . 'build/classicFormDesignJs.asset.php';

        return $scriptAsset['dependencies'];
    }
}
