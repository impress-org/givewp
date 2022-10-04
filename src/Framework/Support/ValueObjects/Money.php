<?php

namespace Give\Framework\Support\ValueObjects;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Currency;
use Money\Currency as VendorCurrency;
use Money\Money as VendorMoney;

/**
 * A decorator class for the vendor Money class which adds additional formatting and other convenience methods. Try and
 * keep the vendor Money logic in the Currency facade.
 *
 * @since 2.20.0
 *
 * @method bool equals(Money $money )
 * @method Money subtract(Money $money)
 * @method Money add(Money $money)
 *
 * @mixin VendorMoney
 */
class Money
{
    /**
     * @var VendorMoney
     */
    private $amount;

    /**
     * @since 2.20.0
     *
     * @param string|int $amount
     * @param string|VendorCurrency $currency
     */
    public function __construct($amount, $currency)
    {
        if (!$currency instanceof VendorCurrency) {
            $currency = new VendorCurrency($currency);
        }

        $this->amount = new VendorMoney($amount, $currency);
    }

    /**
     * Returns the amount in the smallest unit of the currency.
     *
     * @since 2.20.0
     *
     * @return string
     */
    public function formatToMinorAmount()
    {
        return $this->amount->getAmount();
    }

    /**
     * Returns a new instance converted to the system base currency
     *
     * @since 2.20.0
     *
     * @param $exchangeRate
     *
     * @return Money
     */
    public function inBaseCurrency($exchangeRate = 1)
    {
        return self::fromMoney(Currency::convertToBaseCurrency($this->amount, $exchangeRate));
    }

    /**
     * Returns the amount in a decimal format, not including any currency symbols:
     * - $1,500.25 -> 1500.25
     *
     * @since 2.20.0
     *
     * @return string
     */
    public function formatToDecimal()
    {
        return Currency::formatToDecimal($this->amount);
    }

    /**
     * Formats the amount to a currency format, including currency symbols, in the given locale.
     *
     * @since 2.20.0
     *
     * @param string|null $locale
     *
     * @return string
     */
    public function formatToLocale($locale = null)
    {
        return Currency::formatToLocale($this->amount, $locale);
    }

    /**
     * Passes all unknown method calls to the underlying vendor Money instance.
     * Any instance of this class in arguments will be converted to the underlying vendor Money instance.
     * If the returned value is an instance of the vendor Money class, it will be converted to an instance of this class.
     *
     * @since 2.20.0
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->amount, $name)) {
            throw new InvalidArgumentException("Invalid method: $name");
        }

        if (!empty($arguments)) {
            $arguments = array_map(static function ($argument) {
                if ($argument instanceof Money) {
                    return $argument->amount;
                }

                return $argument;
            }, $arguments);
        }

        $value = $this->amount->$name(...$arguments);

        if ($value instanceof VendorMoney) {
            return self::fromMoney($value);
        }

        return $value;
    }

    /**
     * Returns a new, immutable instance from a vendor Money instance
     *
     * @since 2.20.0
     *
     * @param VendorMoney $money
     *
     * @return Money
     */
    public static function fromMoney(VendorMoney $money)
    {
        return new self($money->getAmount(), $money->getCurrency());
    }

    /**
     * Creates a new Money instance from a decimal amount
     *
     * @since 2.20.0
     *
     * @param string|float|int $amount
     */
    public static function fromDecimal($amount, string $currency): Money
    {
        return self::fromMoney(Currency::parseFromDecimal($amount, $currency));
    }
}
