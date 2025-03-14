<?php

namespace Give\FeatureFlags\OptionBasedFormEditor\Settings;

/**
 * @since 3.18.0
 */
class General extends AbstractOptionBasedFormEditorSettings
{
    /**
     * @since 3.18.0
     */
    public function getDisabledOptionIds(): array
    {
        return [
            // General Section
            'override_legacy_donation_management_pages',
            // Currency Section
            'auto_format_currency',
            'currency_position',
            'thousands_separator',
            'decimal_separator',
            'number_decimals',
            'currency_preview',
            // Access-control
            'session_lifetime',
            'limit_display_donations',
        ];
    }
}
