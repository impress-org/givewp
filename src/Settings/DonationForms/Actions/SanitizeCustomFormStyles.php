<?php

namespace Give\Settings\DonationForms\Actions;

/**
 * @since 4.5.0
 */
class SanitizeCustomFormStyles
{
    /**
     * @since 4.5.0
     */
    public function __invoke($value, $option, $raw_value)
    {
        if ($option['id'] === 'custom_form_styles') {
            return wp_strip_all_tags($raw_value);
        }

        return $value;
    }
}
