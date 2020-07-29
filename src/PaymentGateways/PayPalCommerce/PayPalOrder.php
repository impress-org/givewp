<?php
namespace Give\PaymentGateways\PayPalCommerce;

use Give\Helpers\ArrayDataSet;
use InvalidArgumentException;
use stdClass;

/**
 * Class PayPalOrder
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @property-read string $id
 * @property-read string $intent
 * @property-read stdClass $purchaseUnit
 * @property-read stdClass $payer
 * @property-read string $createTime
 * @property-read string $updateTime
 * @property-read string $links
 * @property-read string $status
 */
class PayPalOrder {
	/**
	 * Create PayPalOrder object from given array.
	 *
	 * @since 2.8.0
	 *
	 * @param $array
	 *
	 * @return PayPalOrder
	 */
	public static function fromArray( $array ) {
		$order = new static();

		$order->validate( $array );
		$array = ArrayDataSet::camelCaseKeys( $array );

		foreach ( $array as $itemName => $value ) {
			if ( 'purchaseUnits' === $itemName ) {
				$value = current( $value );
			}

			$order->{$itemName} = $value;
		}

		return $order;
	}

	/**
	 * Get payment detail from PayPal order.
	 *
	 * @since 2.8.0
	 *
	 *
	 * @return PayPalPayment
	 */
	public function getPayment() {
		if ( property_exists( $this->purchaseUnits, 'payments' ) ) {
			$payments = $this->purchaseUnits->payments;

			if ( property_exists( $payments, 'captures' ) ) {
				return PayPalPayment::fromArray( (array) current( $payments->captures ) );
			}
		}

		return null;
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
		$required = [ 'id', 'intent', 'purchase_units', 'payer', 'create_time', 'update_time', 'status', 'links' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'To create a PayPalOrder object, please provide valid id, intent, payer, create_time, update_time, status, links and purchase_units', 'give' ) );
		}
	}
}
