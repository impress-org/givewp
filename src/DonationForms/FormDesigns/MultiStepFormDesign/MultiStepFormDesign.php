<?php

namespace Give\DonationForms\FormDesigns\MultiStepFormDesign;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @since 3.0.0
 */
class MultiStepFormDesign extends FormDesign
{
    /**
     * @since 3.0.0
     */
    protected $isMultiStep = true;

    /**
     * @since 3.6.0
     */
    protected $includeHeaderInMultiStep = true;

    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'multi-step';
    }

    /**
     * @since 3.0.0
     */
    public static function name(): string
    {
        return __('Multi-Step', 'give');
    }

    /**
     * @since 3.0.0
     */
    public function css(): string
    {
        return GIVE_PLUGIN_URL . 'build/multiStepFormDesignCss.css';
    }

    /**
     * @since 3.0.0
     */
    public function dependencies(): array
    {
        $scriptAsset = require GIVE_PLUGIN_DIR . 'build/multiStepFormDesignJs.asset.php';

        return $scriptAsset['dependencies'];
    }
}
