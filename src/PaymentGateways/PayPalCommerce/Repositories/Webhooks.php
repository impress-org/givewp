<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\Route\PayPalWebhooks;
use Give\Route\PayPalWebhooks as WebhooksRoute;

class Webhooks {
	/**
	 * The wp_options key the webhook id is stored under
	 *
	 * @since 2.8.0
	 */
	const OPTION_KEY = 'give_paypal_commerce_webhook_id';

	/**
	 * @since 2.8.0
	 *
	 * @var PayPalWebhooks
	 */
	private $webhookRoute;

	/**
	 * The webhook events registered with PayPal
	 *
	 * @since 2.8.0
	 *
	 * @var string[]
	 */
	private $webhookEvents = [
		'CHECKOUT.ORDER.APPROVED',
		'PAYMENT.CAPTURE.COMPLETED',
		'PAYMENT.CAPTURE.DENIED',
		'PAYMENT.CAPTURE.REFUNDED',
	];

	/**
	 * Webhooks constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param PayPalWebhooks $webhookRoute
	 */
	public function __construct( WebhooksRoute $webhookRoute ) {
		$this->webhookRoute = $webhookRoute;
	}

	/**
	 * Verifies with PayPal that the given event is securely from PayPal and not some sneaking sneaker
	 *
	 * @see https://developer.paypal.com/docs/api/webhooks/v1/#verify-webhook-signature
	 * @since 2.8.0
	 *
	 * @param string $token
	 * @param object $event The event to verify
	 * @param array  $headers The request headers
	 *
	 * @return bool
	 */
	public function verifyEventSignature( $token, $event, $headers ) {
		$verificationUrl = give_is_test_mode()
			? 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature'
			: 'https://api.paypal.com/v1/notifications/verify-webhook-signature';

		$webhookId = $this->getWebhookId();

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
	 * @param string $token
	 *
	 * @return mixed
	 */
	public function createWebhook( $token ) {
		$apiUrl = give_is_test_mode()
			? 'https://api.sandbox.paypal.com/v1/notifications/webhooks'
			: 'https://api.paypal.com/v1/notifications/webhooks';

		$webhookUrl = $this->webhookRoute->getRouteUrl();

		$response = wp_remote_post(
			$apiUrl,
			[
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer $token",
				],
				'body'    => json_encode(
					[
						'url'         => $webhookUrl,
						'event_types' => array_map(
							static function ( $eventType ) {
								return [
									'name' => $eventType,
								];
							},
							$this->webhookEvents
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
	 * @param string $token
	 * @param string $webhookId
	 *
	 * @return bool Whether or not the deletion was successful
	 */
	public function deleteWebhook( $token, $webhookId ) {
		$apiUrl = give_is_test_mode()
			? "https://api.sandbox.paypal.com/v1/notifications/webhooks/$webhookId"
			: "https://api.paypal.com/v1/notifications/webhooks/$webhookId";

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

	/**
	 * Stores the webhook id
	 *
	 * @since 2.8.0
	 *
	 * @param string $id
	 */
	public function saveWebhookId( $id ) {
		update_option( self::OPTION_KEY, $id, false );
	}

	/**
	 * Returns the stored webhook id
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function getWebhookId() {
		return get_option( self::OPTION_KEY );
	}

	/**
	 * Deletes the webhook id
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public function deleteWebhookId() {
		return delete_option( self::OPTION_KEY );
	}

	/**
	 * Adds an event to be listened for by the webhook
	 *
	 * @since 2.8.0
	 *
	 * @param string $event
	 */
	public function addWebhookEvent( $event ) {
		if ( ! in_array( $event, $this->webhookEvents ) ) {
			$this->webhookEvents[] = $event;
		}
	}
}
