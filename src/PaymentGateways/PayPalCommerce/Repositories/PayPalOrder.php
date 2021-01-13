<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use InvalidArgumentException;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Exception;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
use function give_record_gateway_error as logError;

/**
 * Class PayPalOrder
 * @since 2.9.0
 * @package Give\PaymentGateways\PayPalCommerce\Repositories
 *
 */
class PayPalOrder {
	/**
	 * @since 2.9.0
	 *
	 * @var PayPalClient
	 */
	private $paypalClient;

	/**
	 * @since 2.9.0
	 *
	 * @var MerchantDetail
	 */
	private $merchantDetails;

	/**
	 * PayPalOrder constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param MerchantDetail $merchantDetails
	 *
	 * @param PayPalClient   $paypalClient
	 */
	public function __construct( PayPalClient $paypalClient, MerchantDetail $merchantDetails ) {
		$this->paypalClient    = $paypalClient;
		$this->merchantDetails = $merchantDetails;
	}

	/**
	 * Approve order.
	 *
	 * @since 2.9.0
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
			logError(
				'Capture PayPal Commerce payment failure',
				sprintf(
					'<strong>Response</strong><pre>%1$s</pre>',
					print_r( json_decode( $ex->getMessage(), true ), true )
				)
			);

			throw $ex;
		}
	}

	/**
	 * Create order.
	 *
	 * @since 2.9.0
	 *
	 * @param array $array
	 *
	 * @return string
	 * @throws Exception
	 */
	public function createOrder( $array ) {
		$this->validateCreateOrderArguments( $array );

		$request = new OrdersCreateRequest();
		$request->payPalPartnerAttributionId( give( 'PAYPAL_COMMERCE_ATTRIBUTION_ID' ) );
		$request->body = [
			'intent'              => 'CAPTURE',
			'purchase_units'      => [
				[
					'reference_id'        => get_post_field( 'post_name', $array['formId'] ),
					'description'         => $array['formTitle'],
					'amount'              => [
						'value'         => give_maybe_sanitize_amount( $array['donationAmount'], [ 'currency' => give_get_currency( $array['formId'] ) ] ),
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
			logError(
				'Create PayPal Commerce order failure',
				sprintf(
					'<strong>Request</strong><pre>%1$s</pre><br><strong>Response</strong><pre>%2$s</pre>',
					print_r( $request->body, true ),
					print_r( json_decode( $ex->getMessage(), true ), true )
				)
			);

			throw $ex;
		}
	}

	/**
	 * Refunds a processed payment
	 *
	 * @since 2.9.0
	 *
	 * @param $captureId
	 *
	 * @return string The id of the refund
	 * @throws Exception
	 */
	public function refundPayment( $captureId ) {
		$refund = new CapturesRefundRequest( $captureId );

		try {
			return $this->paypalClient->getHttpClient()->execute( $refund )->result->id;
		} catch ( Exception $exception ) {
			logError(
				'Create PayPal Commerce payment refund failure',
				sprintf(
					'<strong>Response</strong><pre>%1$s</pre>',
					print_r( json_decode( $exception->getMessage(), true ), true )
				)
			);

			throw $exception;
		}
	}

	/**
	 * Validate argument given to create PayPal order.
	 *
	 * @since 2.9.0
	 *
	 * @param array $array
	 *
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
