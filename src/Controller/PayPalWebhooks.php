<?php

namespace Give\Controller;

use Exception;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\EventListener;

class PayPalWebhooks {
	/**
	 * Handles all webhook event requests. First it verifies that authenticity of the event with
	 * PayPal, and then it passes the event along to the appropriate listener to finish.
	 *
	 * @since 2.8.0
	 *
	 * @throws Exception
	 */
	public function handle() {
		$event = json_decode( file_get_contents( 'php://input' ), false );

		if ( ! $this->verifyEventSignature( $event ) ) {
			throw new Exception( 'Failed event verification' );
		}

		$handlerClass = 'Give\\PaymentGateways\\PayPalCommerce\\Webhooks\\Listeners\\'
						. $this->deriveHandlerClass( $event->event_type );

		/** @var EventListener $handler */
		$handler = new $handlerClass();

		$handler->processEvent( $event );
	}

	/**
	 *
	 *
	 * @param object $event
	 *
	 * @return bool
	 */
	private function verifyEventSignature( $event ) {
		$verificationUrl = give_is_test_mode()
			? 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature'
			: 'https://api.paypal.com/v1/notifications/verify-webhook-signature';

		// TODO: Retrieve the actual token and webhook ID
		$token     = 'abc';
		$webhookId = '';

		$headers = getallheaders();

		$response = wp_remote_post(
			$verificationUrl,
			[
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer $token",
				],
				'body'    => wp_json_encode(
					[
						'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'],
						'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'],
						'cert_url'          => $headers['PAYPAL-CERT-URL'],
						'auth_algo'         => $headers['AYPAL-AUTH-ALGO'],
						'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'],
						'webhook_id'        => $webhookId,
						'webhook_event'     => $event,
					]
				),
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response = json_decode( $response['body'], false );

		return $response->verification_status === 'SUCCESS';
	}

	/**
	 * This takes an event type such as CHECKOUT.ORDER.APPROVED, breaks it apart, and puts it back
	 * together as a studly cased class: CheckoutOrderApproved.
	 *
	 * @param string $event_type
	 *
	 * @return string
	 */
	private function deriveHandlerClass( $event_type ) {
		return implode(
			'',
			array_map(
				function ( $name ) {
					return ucfirst( strtolower( $name ) );
				},
				explode( '.', $event_type )
			)
		);
	}
}
