<?php

namespace Give\Settings\DonationForms\Actions;

/**
 * @unreleased
 */
class SanitizeCustomFormStyles
{
    /**
     * @unreleased
     */
    public function __invoke($value, $option, $raw_value)
    {
        if ($option['id'] === 'custom_form_styles') {
            return wp_strip_all_tags($raw_value);
        }

        return $value;
    }
}