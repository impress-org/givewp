<?php

namespace Give\ValueObjects;

/**
 * Class Money
 * @package Give\ValueObjects
 *
 * @since 2.9.0
 * @since 2.11.0 Refactored to make the minor amount the base.
 */
class Money
{
    /**
     * The amount in smallest unit of currency.
     * @var int
     */
    protected $minorAmount;

    /**
     * @var array
     */
    protected $currencyData;

    /**
     * Money constructor.
     *
     * @param int   $minorAmount
     * @param array $currencyData
     */
    public function __construct($minorAmount, $currencyData)
    {
        $this->minorAmount = $minorAmount;
        $this->currencyData = $currencyData;
    }

    /**
     * Get amount in smallest unit of currency.
     *
     * @sicne 2.9.0
     * @since 2.11.0 Round minor amount to account for floating point precision.
     *
     * @return int
     */
    public function getMinorAmount()
    {
        return $this->minorAmount;
    }

    /**
     * Get amount in smallest unit of currency.
     *
     * @sicne 2.9.0
     *
     * @return string
     */
    public function getAmount()
    {
        if ($this->currencyData['setting']['number_decimals']) {
            return $this->minorAmount / (10 ** $this->currencyData['setting']['number_decimals']);
        }

        return $this->minorAmount;
    }

    // Static Methods and Factories

    /**
     * @since 2.9.0
     * @since 2.11.0 Converts the amount to a minor amount when creating an instance.
     *
     * @param int|string $amount Amount value without currency formatting
     * @param string     $currency
     *
     * @return Money
     */
    public static function of($amount, $currency)
    {
        $currencyData = self::getCurrencyData($currency);

        /**
         * When working with float values, be careful when casting to an integer.
         * Due to "floating point precision", the output may not match the expected value.
         *
         * @link https://www.php.net/manual/en/language.types.float.php
         *  This can lead to confusing results:
         *      for example, floor((0.1+0.7)*10) will usually return 7 instead of the expected 8,
         *      since the internal representation will be something like 7.9999999999999991118....
         */
        $amount = absint(
            round($amount * (10 ** $currencyData['setting']['number_decimals']))
        );

        return new static($amount, $currencyData);
    }

    /**
     * @since 2.9.0
     *
     * @param int|string $minorAmount
     * @param string     $currency
     *
     * @return Money
     */
    public static function ofMinor($minorAmount, $currency)
    {
        return new static($minorAmount, self::getCurrencyData($currency));
    }

    /**
     * Retrieves the currency data for a given currency with some optimizations to avoid loading all the currencies more
     * than once.
     *
     * @since 2.9.0
     *
     * @param $currency
     *
     * @return array
     */
    private static function getCurrencyData($currency)
    {
        static $currenciesData = null;

        if ($currenciesData === null) {
            $currenciesData = give_get_currencies('all');
        }

        return $currenciesData[$currency];
    }
}
