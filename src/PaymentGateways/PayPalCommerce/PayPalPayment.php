<?php
namespace Give\PaymentGateways\PayPalCommerce;

use Give\Helpers\ArrayDataSet;
use InvalidArgumentException;

/**
 * Class PayPalPayment
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @property-read string $id
 * @property-read string $status
 * @property-read string $amount
 * @property-read string $createTime
 * @property-read string $updateTime
 */
class PayPalPayment {
	/**
	 * Create PayPalPayment object from given array.
	 *
	 * @since 2.8.0
	 *
	 * @param $array
	 *
	 * @return PayPalPayment
	 */
	public static function fromArray( $array ) {
		$payment = new static();

		$payment->validate( $array );

		$array = ArrayDataSet::camelCaseKeys( $array );

		foreach ( $array as $itemName => $value ) {
			$payment->{$itemName} = $value;
		}

		return $payment;
	}

	/**
	 * Validate order given in array format.
	 *
	 * @since 2.8.0
	 *
	 * @param array $array
	 * @throws InvalidArgumentException
	 */
	private function validate( $array ) {
		$required = [ 'id', 'amount', 'status', 'create_time', 'update_time', 'links' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'To create a PayPalPayment object, please provide valid id, amount, status, create_time, update_time and links', 'give' ) );
		}
	}
}
