<?php
namespace Give\PaymentGateways\PayPalCommerce;

use Exception;
use Give\Helpers\ArrayDataSet;
use InvalidArgumentException;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use stdClass;

/**
 * Class PayPalOrder
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.8.0
 */
class PayPalOrder {
	/**
	 * Order Id.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Order intent.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	public $intent;

	/**
	 * Order status.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Order creation time.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	public $createTime;

	/**
	 * Order update time.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	public $updateTime;

	/**
	 * PayPal Order action links.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	public $links;

	/**
	 * Payer information.
	 *
	 * @since 2.8.0
	 *
	 * @var stdClass
	 */
	public $payer;

	/**
	 * Order purchase unit details.
	 *
	 * @since 2.8.0
	 *
	 * @var stdClass
	 */
	public $purchaseUnit;

	/**
	 * @since 2.8.0
	 *
	 * @var PayPalClient
	 */
	private $paypalClient;

	/**
	 * @since 2.8.0
	 *
	 * @var MerchantDetail
	 */
	private $merchantDetails;

	/**
	 * PayPalOrder constructor.
	 *
	 * @param  PayPalClient  $paypalClient
	 * @param  MerchantDetail  $merchantDetails
	 */
	public function __construct( PayPalClient $paypalClient, MerchantDetail $merchantDetails ) {
		$this->paypalClient    = $paypalClient;
		$this->merchantDetails = $merchantDetails;
	}

	/**
	 * Approve order.
	 *
	 * @since 2.8.0
	 *
	 * @param string $orderId
	 *
	 * @return string
	 * @throws Exception
	 */
	public function approveOrder( $orderId ) {
		$request = new OrdersCaptureRequest( $orderId );

		try {
			return $this->paypalClient->getHttpClient()->execute( $request )->result;
		} catch ( Exception $ex ) {
			throw $ex;
		}
	}

	/**
	 * Create order.
	 *
	 * @since 2.8.0
	 *
	 * @param array $array
	 *
	 * @return string
	 * @throws Exception
	 */
	public function createOrder( $array ) {
		$this->validateCreateOrderArguments( $array );

		$request = new OrdersCreateRequest();
		$request->payPalPartnerAttributionId( PartnerDetails::$attributionId );
		$request->body = [
			'intent'              => 'CAPTURE',
			'purchase_units'      => [
				[
					'reference_id'        => get_post_field( 'post_name', $array['formId'] ),
					'description'         => '',
					'amount'              => [
						'value'         => give_maybe_sanitize_amount( $array['donationAmount'] ),
						'currency_code' => give_get_currency( $array['formId'] ),
					],
					'payee'               => [
						'email_address' => $this->merchantDetails->merchantId,
						'merchant_id'   => $this->merchantDetails->merchantIdInPayPal,
					],
					'payer'               => [
						'given_name'    => $array['payer']['firstName'],
						'surname'       => $array['payer']['lastName'],
						'email_address' => $array['payer']['email'],
					],
					'payment_instruction' => [
						'disbursement_mode' => 'INSTANT',
					],
				],
			],
			'application_context' => [
				'shipping_preference' => 'NO_SHIPPING',
				'user_action'         => 'PAY_NOW',
			],
		];

		try {
			return $this->paypalClient->getHttpClient()->execute( $request )->result->id;
		} catch ( Exception $ex ) {
			throw $ex;
		}
	}

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
		/* @var PayPalOrder $order */
		$order = give( __CLASS__ );

		$order->validate( $array );
		$array = ArrayDataSet::camelCaseKeys( $array );

		foreach ( $array as $itemName => $value ) {
			if ( 'purchaseUnits' === $itemName ) {
				// We will always have single unit in order.
				$itemName = 'purchaseUnit';
				$value    = current( $value );
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
		return PayPalPayment::fromArray( (array) current( $this->purchaseUnit->payments->captures ) );

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

	/**
	 * Validate argument given to create PayPal order.
	 *
	 * @since 2.8.0
	 *
	 * @param array $array
	 * @throws InvalidArgumentException
	 */
	private function validateCreateOrderArguments( $array ) {
		$required = [ 'formId', 'donationAmount', 'payer' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'To create a paypal order, please provide formId, donationAmount and payer', 'give' ) );
		}
	}
}
