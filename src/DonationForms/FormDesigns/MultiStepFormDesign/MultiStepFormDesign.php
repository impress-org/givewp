<?php

namespace Give\DonationForms\FormDesigns\MultiStepFormDesign;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @since 0.4.0
 */
class MultiStepFormDesign extends FormDesign
{
    protected $isMultiStep = true;

    /**
     * @since 0.4.0
     */
    public static function id(): string
    {
        return 'multi-step';
    }

    /**
     * @since 0.4.0
     */
    public static function name(): string
    {
        return __('Multi-Step', 'give');
    }

    /**
     * @since 0.4.0
     */
    public function css(): string
    {
        return GIVE_PLUGIN_URL . 'build/multiStepFormDesignCss.css';
    }

    /**
     * @since 0.4.0
     */
    public function js(): string
    {
        return GIVE_PLUGIN_URL . 'build/multiStepFormDesignJs.js';
    }

    /**
     * @since 0.4.0
     */
    public function dependencies(): array
    {
        $scriptAsset = require GIVE_PLUGIN_DIR . 'build/multiStepFormDesignJs.asset.php';

        return $scriptAsset['dependencies'];
    }
}
