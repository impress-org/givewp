<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

class Webhooks {
	/**
	 * Verifies with PayPal that the given event is securely from PayPal and not some sneaking sneaker
	 *
	 * @see https://developer.paypal.com/docs/api/webhooks/v1/#verify-webhook-signature
	 * @since 2.8.0
	 *
	 * @param array  $headers The request headers
	 * @param object $event The event to verify
	 *
	 * @return bool
	 */
	public function verifyEventSignature( $event, $headers ) {
		$verificationUrl = give_is_test_mode()
			? 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature'
			: 'https://api.paypal.com/v1/notifications/verify-webhook-signature';

		// TODO: Retrieve the actual token and webhook ID
		$token     = 'abc';
		$webhookId = '';

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
	 * Creates a webhook with the given event types registered.
	 *
	 * @see https://developer.paypal.com/docs/api/webhooks/v1/#webhooks_post
	 * @since 2.8.0
	 *
	 * @param string $url
	 * @param array  $eventTypes a sequential array of events to register
	 *
	 * @return mixed
	 */
	public function createWebhook( $url, array $eventTypes ) {
		$apiUrl = give_is_test_mode()
			? 'https://api.sandbox.paypal.com/v1/notifications/webhooks'
			: 'https://api.paypal.com/v1/notifications/webhooks';

		// TODO: Retrieve the actual token and webhook ID
		$token = 'abc';

		$response = wp_remote_post(
			$apiUrl,
			[
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer $token",
				],
				'body'    => wp_json_encode(
					[
						'url'         => $url,
						'event_types' => array_map(
							static function ( $eventType ) {
								return [
									'name' => $eventType,
								];
							},
							$eventTypes
						),
					]
				),
			]
		);

		$response = json_decode( $response['body'], false );

		return $response->id;
	}

	/**
	 * Deletes the webhook with the given id.
	 *
	 * @since 2.8.0
	 *
	 * @param string $webhookId
	 *
	 * @return bool Whether or not the deletion was successful
	 */
	public function deleteWebhook( $webhookId ) {
		$apiUrl = give_is_test_mode()
			? "https://api.sandbox.paypal.com/v1/notifications/webhooks/$webhookId"
			: "https://api.paypal.com/v1/notifications/webhooks/$webhookId";

		// TODO: Retrieve the actual token and webhook ID
		$token = 'abc';

		$response = wp_remote_request(
			$apiUrl,
			[
				'method'  => 'DELETE',
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer $token",
				],
			]
		);

		$code = wp_remote_retrieve_response_code( $response );

		return $code >= 200 && $code < 300;
	}
}
