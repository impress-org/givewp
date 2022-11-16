<?php

namespace Give\NextGen\DonationForm\FormDesigns\DeveloperFormDesign;

use Give\NextGen\Framework\FormDesigns\FormDesign;

/**
 * @unreleased
 */
class DeveloperFormDesign extends FormDesign
{
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
        return __('Developer', 'give');
    }

    /**
     * @unreleased
     */
    public function css(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/developerFormDesignCss.css';
    }
}
