<?php

namespace Give\FeatureFlags\LegacyForms\Settings;

/**
 * @unreleased
 */
class General
{
    /**
     * @unreleased
     */
    public function disableSections(array $sections): array
    {
        $disabledSectionIds = [
            'access-control',
        ];

        foreach ($sections as $key => $value) {
            if (in_array($key, $disabledSectionIds)) {
                unset($sections[$key]);
            }
        }

        return $sections;
    }

    /**
     * @unreleased
     */
    public function disableOptions(array $options): array
    {
        $disabledOptionIds = [
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

        foreach ($options as $key => $value) {
            if (isset($value['id']) && in_array($value['id'], $disabledOptionIds)) {
                unset($options[$key]);
            }
        }

        return $options;
    }
}
