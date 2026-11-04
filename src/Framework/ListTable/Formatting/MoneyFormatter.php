<?php

namespace Give\Framework\ListTable\Formatting;

use Give\Framework\ListTable\Views\Placeholder;
use Give\Framework\Support\ValueObjects\Money;

/**
 * The one way list tables write money. It follows the currency settings of the site, the same way
 * receipts and reports do.
 *
 * @since TBD
 */
final class MoneyFormatter
{
    /**
     * @since TBD
     */
    public static function format(?Money $amount): string
    {
        if ($amount === null) {
            return Placeholder::dash(__('No amount', 'give'));
        }

        $code = $amount->getCurrency()->getCode();

        return give_currency_filter(
            give_format_amount($amount->formatToDecimal(), ['currency' => $code]),
            ['currency_code' => $code, 'decode_currency' => true]
        );
    }

    /**
     * @since TBD
     *
     * @param string|int|float|null $amount A decimal amount such as "12.50".
     * @param string|null $currency Defaults to the currency of the site.
     */
    public static function fromDecimal($amount, ?string $currency = null): string
    {
        if ($amount === null || $amount === '') {
            return Placeholder::dash(__('No amount', 'give'));
        }

        return self::format(Money::fromDecimal($amount, $currency ?: give_get_currency()));
    }
}
