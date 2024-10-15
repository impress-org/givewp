<?php

namespace Give\FeatureFlags\OptionBasedFormEditor\Settings;

/**
 * @unreleased
 */
class General extends AbstractOptionBasedFormEditorSettings
{
    /**
     * @unreleased
     */
    public function getNewDefaultSection(): string
    {
        return '';
    }
    
    /**
     * @unreleased
     */
    public function getDisabledSectionIds(): array
    {
        return [
            'access-control',
        ];
    }

    /**
     * @unreleased
     */
    public function getDisabledOptionIds(): array
    {
        return [
            //General Section
            'success_page',
            'failure_page',
            // Currency Section
            'auto_format_currency',
            'currency_position',
            'thousands_separator',
            'decimal_separator',
            'number_decimals',
            'currency_preview',
        ];
    }
}
