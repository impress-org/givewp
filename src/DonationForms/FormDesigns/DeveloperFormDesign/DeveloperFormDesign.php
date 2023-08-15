<?php

namespace Give\DonationForms\FormDesigns\DeveloperFormDesign;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @since 0.1.0
 */
class DeveloperFormDesign extends FormDesign
{
    /**
     * @since 0.1.0
     */
    public static function id(): string
    {
        return 'developer';
    }

    /**
     * @since 0.1.0
     */
    public static function name(): string
    {
        return __('Developer', 'give');
    }
}
