<?php
namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\PaymentGateways\PayPalCommerce\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\PartnerDetails;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use InvalidArgumentException;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Exception;

/**
 * Class PayPalOrder
 * @package Give\PaymentGateways\PayPalCommerce\Repositories
 *
 * @since 2.8.0
 */
class PayPalOrder {
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
	 *
	 * @since 2.8.0
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
