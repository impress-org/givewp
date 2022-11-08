<?php

namespace Give\NextGen\DonationForm\FormTemplates\DeveloperFormTemplate;

use Give\NextGen\Framework\FormTemplates\FormTemplate;

/**
 * @unreleased
 */
class DeveloperFormTemplate extends FormTemplate {
    /**
     * @unreleased
     */
     public static function id(): string
    {
        return 'developer';
    }

    /**
     * @unreleased
     */
    public static function name(): string
    {
        return __('Developer Template', 'give');
    }

    /**
     * @unreleased
     */
    public function css(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/developerTemplateCss.css';
    }
}
