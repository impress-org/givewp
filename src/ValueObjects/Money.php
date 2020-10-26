<?php

namespace Give\ValueObjects;

/**
 * Class Money
 * @package Give\ValueObjects
 *
 * @since 2.9.0
 */
class Money {
	/**
	 * @var int|string
	 */
	private $amount;

	/**
	 * @var int
	 */
	private $minorAmount;

	/**
	 * @var string
	 */
	private $currency;

	/**
	 * @var array
	 */
	private $currencyData;

	/**
	 * Return Money class object.
	 *
	 * @since 2.9.0
	 *
	 * @param int|string $amount Amount value without currency formatting
	 * @param string     $currency
	 *
	 * @return Money
	 */
	public static function of( $amount, $currency ) {
		$object = new static();

		$object->amount       = $amount;
		$object->currency     = $currency;
		$object->currencyData = self::getCurrencyData( $currency );

		return $object;
	}

	/**
	 * Return Money class object.
	 *
	 * @since 2.9.0
	 *
	 * @param int|string $amount
	 * @param string     $currency
	 *
	 * @return Money
	 */
	public static function ofMinor( $amount, $currency ) {
		$object = new static();

		$object->minorAmount  = $amount;
		$object->currency     = $currency;
		$object->currencyData = self::getCurrencyData( $currency );

		return $object;
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
	private static function getCurrencyData( $currency ) {
		static $currenciesData = null;

		if ( $currenciesData === null ) {
			$currenciesData = give_get_currencies( 'all' );
		}

		return $currenciesData[ $currency ];
	}

	/**
	 * Get amount in smallest unit of currency.
	 *
	 * @sicne 2.9.0
	 *
	 * @return int
	 */
	public function getMinorAmount() {
		if ( $this->minorAmount ) {
			return $this->minorAmount;
		}

		$decimals = $this->getNumberDecimals();

		$tensMultiplier = 10 ** $decimals;

		return $this->minorAmount = absint( $this->amount * $tensMultiplier );
	}

	/**
	 * Get amount in smallest unit of currency.
	 *
	 * @sicne 2.9.0
	 *
	 * @return string
	 */
	public function getAmount() {
		if ( $this->amount ) {
			return $this->amount;
		}

		$decimals = $this->getNumberDecimals();

		$tensMultiplier = 10 ** $decimals;

		return $this->amount = absint( $this->minorAmount / $tensMultiplier );
	}

	/**
	 * Returns the number of decimals based on the currency
	 *
	 * @since 2.9.0
	 *
	 * @return int
	 */
	private function getNumberDecimals() {
		return $this->currencyData['setting']['number_decimals'];
	}
}
