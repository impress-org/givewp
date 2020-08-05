<?php

namespace Give\Onboarding\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\Onboarding\SettingsRepository;
use Give\Onboarding\Helpers\FormatList;
use Give\Onboarding\Helpers\CountryCode;

/**
 * @since 2.8.0
 */
class StripeWebhookRecievedRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'onboarding/stripe-webhook-recieved';

	/** @var SettingsRepository */
	protected $settingsRepository;

	/**
	 * @param SettingsRepository $settingsRepository
	 *
	 * @since 2.8.0
	 */
	public function __construct( SettingsRepository $settingsRepository ) {
		$this->settingsRepository = $settingsRepository;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function handleRequest( WP_REST_Request $request ) {

		$this->ensureStripeEnabled();

		\Stripe\Stripe::setApiKey(
			give_stripe_get_secret_key()
		);

		$this->triggerStripeTestEvent(
			$this->getStripeTestCustomerID()
		);

		return [
			'webhookRecieved' => $this->getWebhookRecieved(),
		];
	}

	/**
	 * @since 2.8.0
	 *
	 * @return void
	 */
	protected function ensureStripeEnabled() {
		$gateways = $this->settingsRepository->has( 'gateways' )
			? $this->settingsRepository->get( 'gateways' )
			: [];

		if ( ! array_key_exists( 'stripe', $gateways ) || ! $gateways['stripe'] ) {
			$gateways['stripe'] = 1;
			$this->settingsRepository->set( 'gateways', $gateways );
			$this->settingsRepository->save();
		}
	}

	/**
	 * @since 2.8.0
	 *
	 * @return string $customerID
	 */
	protected function getStripeTestCustomerID() {
		$customerID = get_option( 'give_stripe_webhooks_test_customer_id', false );
		if ( ! $customerID ) {
			$customerCreated = @\Stripe\Customer::create(
				[
					'description' => 'A test customer created by GiveWP to test the webhooks configuration.',
				]
			);
			$customerID      = $customerCreated->id;
			update_option( 'give_stripe_webhooks_test_customer_id', $customerID );
		}
		return $customerID;
	}

	/**
	 * Triggers a test event in Stripe.
	 *
	 * If the request is invalid delete the test customer ID
	 *   so that another is created.
	 *
	 * @param string $customerID The Stripe customer ID
	 *
	 * @return void
	 *
	 * @since 2.8.0
	 */
	protected function triggerStripeTestEvent( $customerID ) {
		try {
			// Make an update to trigger a `customer.updated` event.
			$updated = @\Stripe\Customer::update(
				$customerID,
				[
					'metadata[test_timestamp]' => time(),
				]
			);
		} catch ( \Stripe\Error\InvalidRequest $e ) {
			// Cleanup
			delete_option( 'give_stripe_webhooks_test_customer_id' );
		} catch ( \Exception $e ) {
			// Supress the error.
		}
	}

	/**
	 * @return bool|string
	 *
	 * @since 2.8.0
	 */
	protected function getWebhookRecieved() {
		return $this->settingsRepository->get( 'give_stripe_last_webhook_received_timestamp' );
	}

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			$this->endpoint,
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => function() {
						return true || current_user_can( 'manage_options' );
					},
				],
				'schema' => [ $this, 'getSchema' ],
			]
		);
	}

	public function getSchema() {
		return [
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'onboarding',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => [],
		];
	}
}
