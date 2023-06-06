<?php

namespace Give\DonationForms\FormDesigns\MultiStepFormDesign;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @unreleased
 */
class MultiStepFormDesign extends FormDesign
{
    protected $isMultiStep = true;

    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'multi-step';
    }

    /**
     * @unreleased
     */
    public static function name(): string
    {
        return __('Multi-Step', 'give');
    }

    /**
     * @unreleased
     */
    public function css(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/multiStepFormDesignCss.css';
    }

    /**
     * @unreleased
     */
    public function js(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/multiStepFormDesignJs.js';
    }

    /**
     * @unreleased
     */
    public function dependencies(): array
    {
        $scriptAsset = require GIVE_NEXT_GEN_DIR . 'build/multiStepFormDesignJs.asset.php';

        return $scriptAsset['dependencies'];
    }
}
