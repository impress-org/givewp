<?php

namespace Give\NextGen\DonationForm\FormTemplates\ClassicFormTemplate;

use Give\NextGen\Framework\FormTemplates\FormTemplate;

/**
 * @unreleased
 */
class ClassicFormTemplate extends FormTemplate {
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
        return __('Classic Template', 'give');
    }

    /**
     * @unreleased
     */
    public function css(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/classicTemplateCss.css';
    }

    /**
     * @unreleased
     */
    public function js(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/classicTemplateJs.js';
    }

    /**
     * @unreleased
     */
    public function dependencies(): array
    {
        $scriptAsset = require GIVE_NEXT_GEN_DIR . 'build/classicTemplateJs.asset.php';

        return $scriptAsset['dependencies'];
    }
}
