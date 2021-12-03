<?php

namespace Give\Onboarding\Helpers;

/**
 * Formats an associative array into a JS parsable array of objects.
 *
 * @since 2.8.0
 */
class FormatList
{

    /**
     * Format a JS value/label object where the $key is the `value` and the $value is the `label`.
     *
     * @since 2.8.0
     *
     * @param array $data
     *
     * @return array
     *
     */
    public static function fromKeyValue($data)
    {
        return self::format(
            $data,
            function ($key, $value) {
                return [
                    'value' => $key,
                    'label' => $value,
                ];
            }
        );
    }

    /**
     * Format a JS value/label object where the $key is the `label` and the $value is the `value`.
     *
     * @since 2.8.0
     *
     * @param array $data
     *
     * @return array
     *
     */
    public static function fromValueKey($data)
    {
        return self::format(
            $data,
            function ($key, $value) {
                return [
                    'value' => $value,
                    'label' => $key,
                ];
            }
        );
    }

    /**
     * A higher-order function to format a JS value/label object.
     *
     * @since 2.8.0
     *
     * @param callable $function
     *
     * @param array    $data
     *
     * @return array
     *
     */
    protected static function format($data, $function)
    {
        return array_map(
            $function,
            array_keys($data),
            array_values($data)
        );
    }
}
