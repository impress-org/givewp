<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks;

use Give\Controller\PayPalWebhooks;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give\Route\PayPalWebhooks as WebhooksRoute;

class WebhookChecker {
	/**
	 * @since 2.9.0
	 *
	 * @var Webhooks
	 */
	private $webhooksRepository;

	/**
	 * @since 2.9.0
	 *
	 * @var PayPalWebhooks
	 */
	private $webhooksController;

	/**
	 * @since 2.9.0
	 *
	 * @var WebhooksRoute
	 */
	private $webhooksRoute;

	/**
	 * @since 2.9.0
	 *
	 * @var MerchantDetail
	 */
	private $merchantDetails;

	/**
	 * WebhookChecker constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param Webhooks       $webhooksRepository
	 * @param PayPalWebhooks $webhooksController
	 * @param MerchantDetail $merchantDetails
	 * @param WebhooksRoute  $webhooksRoute
	 */
	public function __construct( Webhooks $webhooksRepository, PayPalWebhooks $webhooksController, MerchantDetail $merchantDetails, WebhooksRoute $webhooksRoute ) {
		$this->webhooksRepository = $webhooksRepository;
		$this->webhooksController = $webhooksController;
		$this->merchantDetails    = $merchantDetails;
		$this->webhooksRoute      = $webhooksRoute;
	}

	/**
	 * Checks whether the webhook configuration has changed. If it has, then update the webhook with PayPal.
	 *
	 * @since 2.9.0
	 */
	public function checkWebhookCriteria() {
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		if ( ! $this->merchantDetails->accessToken ) {
			return;
		}

		$webhookConfig = $this->webhooksRepository->getWebhookConfig();

		if ( $webhookConfig === null ) {
			return;
		}

		$webhookUrl       = $this->webhooksRoute->getRouteUrl();
		$registeredEvents = $this->webhooksController->getRegisteredEvents();

		$hasMissingEvents = ! empty(
			array_merge(
				array_diff( $registeredEvents, $webhookConfig->events ),
				array_diff( $webhookConfig->events, $registeredEvents )
			)
		);

		// Update the webhook if the return url or events have changed
		if ( $webhookUrl !== $webhookConfig->returnUrl || $hasMissingEvents ) {
			$this->webhooksRepository->updateWebhook( $this->merchantDetails->accessToken, $webhookConfig->id );

			$webhookConfig->returnUrl = $webhookUrl;
			$webhookConfig->events    = $registeredEvents;

			$this->webhooksRepository->saveWebhookConfig( $webhookConfig );
		}
	}
}
