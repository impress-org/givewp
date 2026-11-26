<?php

namespace Give\Framework\ListTable\Formatting;

/**
 * Adds the display strings of a list table stats response, so each stats endpoint does not carry its own copy.
 *
 * @since TBD
 */
final class StatsFormatter
{
    /**
     * Adds a "formatted" key with one plain display string for each value: money through the money
     * formatter, everything else through the count formatter, both for the given locale. A missing value counts as zero, so the
     * strings never hold markup.
     *
     * @since TBD
     *
     * @param array<string, int|float|null> $values
     * @param string[] $moneyKeys Keys of $values that hold money.
     * @param string $locale The browser's language.
     *
     * @return array<string, mixed>
     */
    public static function withFormatted(array $values, array $moneyKeys = [], string $locale = ''): array
    {
        $formatted = [];

        foreach ($values as $key => $value) {
            $value = $value ?? 0;

            $formatted[$key] = in_array($key, $moneyKeys, true)
                ? MoneyFormatter::fromDecimal($value, null, $locale)
                : CountFormatter::format($value, $locale);
        }

        $values['formatted'] = $formatted;

        return $values;
    }
}
