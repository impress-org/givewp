<?php

namespace Give\DonationForms\FormDesigns\DeveloperFormDesign;

use Give\Framework\FormDesigns\FormDesign;

/**
 * @since 3.0.0
 */
class DeveloperFormDesign extends FormDesign
{
    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'developer';
    }

    /**
     * @since 3.0.0
     */
    public static function name(): string
    {
        return __('Developer', 'give');
    }
}
