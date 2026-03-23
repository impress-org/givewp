<?php

namespace Give\DonorDashboards\Helpers;

/**
 * Normalize format of location type lists.
 * 4.14.1 Replaces FormatObjectList with formatAsValueLabelPairs
 * @since 2.10.0
 */
class LocationList
{
    public static function getCountries(): array
    {
        $countries = give_get_country_list();
        unset($countries['']);

        return self::formatAsValueLabelPairs($countries);
    }

    public static function getStates($country): array
    {
        $states = give_get_states($country);
        $states[''] = sprintf('%s...', esc_html__('Select', 'give'));

        return self::formatAsValueLabelPairs($states);
    }

    /**
     * Formats an associative array as an array of value/label pairs for JS consumption.
     *
     * 4.14.1
     */
    private static function formatAsValueLabelPairs(array $data): array
    {
        return array_map(
            static function ($key, $value) {
                return [
                    'value' => $key,
                    'label' => $value,
                ];
            },
            array_keys($data),
            array_values($data)
        );
    }
}
