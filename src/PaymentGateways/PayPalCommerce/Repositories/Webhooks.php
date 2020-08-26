<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Exception;
use Give\PaymentGateways\PayPalCommerce\Models\WebhookConfig;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use Give\PaymentGateways\PayPalCommerce\Repositories\Traits\HasMode;
use Give\PaymentGateways\PayPalCommerce\Webhooks\WebhookRegister;
use Give\Route\PayPalWebhooks as WebhooksRoute;

class Webhooks {
	use HasMode;

	/**
	 * @since 2.8.0
	 *
	 * @var WebhooksRoute
	 */
	private $webhookRoute;

	/**
	 * @var WebhookRegister
	 */
	private $webhooksRegister;

	/**
	 * @var PayPalClient
	 */
	private $payPalClient;

	/**
	 * Webhooks constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param WebhooksRoute   $webhookRoute
	 * @param PayPalClient    $payPalClient
	 * @param WebhookRegister $webhooksRegister
	 */
	public function __construct( WebhooksRoute $webhookRoute, PayPalClient $payPalClient, WebhookRegister $webhooksRegister ) {
		$this->webhookRoute     = $webhookRoute;
		$this->payPalClient     = $payPalClient;
		$this->webhooksRegister = $webhooksRegister;
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
		$apiUrl = $this->payPalClient->getApiUrl( 'v1/notifications/verify-webhook-signature' );

		$webhookConfig = $this->getWebhookConfig();

		$response = wp_remote_post(
			$apiUrl,
			[
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer $token",
				],
				'body'    => wp_json_encode(
					[
						'transmission_id'   => $headers['Paypal-Transmission-Id'],
						'transmission_time' => $headers['Paypal-Transmission-Time'],
						'transmission_sig'  => $headers['Paypal-Transmission-Sig'],
						'cert_url'          => $headers['Paypal-Cert-Url'],
						'auth_algo'         => $headers['Paypal-Auth-Algo'],
						'webhook_id'        => $webhookConfig->id,
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
	 * @return WebhookConfig
	 * @throws Exception
	 */
	public function createWebhook( $token ) {
		$apiUrl = $this->payPalClient->getApiUrl( 'v1/notifications/webhooks' );

		$events     = $this->webhooksRegister->getRegisteredEvents();
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
							$events
						),
					]
				),
			]
		);

		$response = json_decode( $response['body'], false );

		if ( ! isset( $response->id ) ) {
			give_record_gateway_error( 'Create PayPal Commerce Webhook Failure', print_r( $response, true ) );

			throw new Exception( 'Failed to create webhook' );
		}

		return new WebhookConfig( $response->id, $webhookUrl, $events );
	}

	/**
	 * Updates the webhook url and events
	 *
	 * @since 2.9.0
	 *
	 * @param string $token
	 * @param string $webhookId
	 *
	 * @return bool Whether the webhook successfully updated or not
	 */
	public function updateWebhook( $token, $webhookId ) {
		$apiUrl = $this->payPalClient->getApiUrl( "v1/notifications/webhooks/$webhookId" );

		$webhookUrl = $this->webhookRoute->getRouteUrl();

		$response = wp_remote_request(
			$apiUrl,
			json_encode(
				[
					'method'  => 'PATCH',
					'headers' => [
						'Content-Type'  => 'application/json',
						'Authorization' => "Bearer $token",
					],
					'body'    => [
						[
							'op'    => 'replace',
							'path'  => '/url',
							'value' => $webhookUrl,
						],
						[
							'op'    => 'replace',
							'path'  => '/event_types',
							'value' => array_map(
								static function ( $eventType ) {
									return [
										'name' => $eventType,
									];
								},
								$this->webhooksRegister->getRegisteredEvents()
							),
						],
					],
				]
			)
		);

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		return ! empty( $response ) && $webhookId === $response['id'];
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
		$apiUrl = $this->payPalClient->getApiUrl( "v1/notifications/webhooks/$webhookId" );

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
	 * Saves the webhook config in the database
	 *
	 * @since 2.9.0
	 *
	 * @param WebhookConfig $config
	 */
	public function saveWebhookConfig( WebhookConfig $config ) {
		update_option( $this->getOptionKey(), $config->toArray(), false );
	}

	/**
	 * Retrieves the WebhookConfig from the database
	 *
	 * @since 2.9.0
	 *
	 * @return WebhookConfig|null
	 */
	public function getWebhookConfig() {
		$data = get_option( $this->getOptionKey(), null );

		return $data ? WebhookConfig::fromArray( $data ) : null;
	}

	/**
	 * Deletes the stored webhook config
	 *
	 * @since 2.9.0
	 */
	public function deleteWebhookConfig() {
		delete_option( $this->getOptionKey() );
	}

	/**
	 * Returns the option key for the given mode
	 *
	 * @return string
	 */
	private function getOptionKey() {
		return "give_paypal_commerce_{$this->mode}_webhook_config";
	}
}
