<?php

namespace Give\DonationForms\FormDesigns\ClassicFormDesign;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @since 0.1.0
 */
class ClassicFormDesign extends FormDesign
{
    /**
     * @since 0.1.0
     */
    public static function id(): string
    {
        return 'classic';
    }

    /**
     * @since 0.1.0
     */
    public static function name(): string
    {
        return __('Classic', 'give');
    }

    /**
     * @since 0.1.0
     */
    public function css(): string
    {
        return GIVE_PLUGIN_URL . 'build/classicFormDesignCss.css';
    }

    /**
     * @since 0.1.0
     */
    public function js(): string
    {
        return GIVE_PLUGIN_URL . 'build/classicFormDesignJs.js';
    }

    /**
     * @since 0.1.0
     */
    public function dependencies(): array
    {
        $scriptAsset = require GIVE_PLUGIN_DIR . 'build/classicFormDesignJs.asset.php';

        return $scriptAsset['dependencies'];
    }
}
