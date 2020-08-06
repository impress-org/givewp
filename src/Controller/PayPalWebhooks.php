<?php

namespace Give\Controller;

use Exception;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\EventListener;
use InvalidArgumentException;

class PayPalWebhooks {
	/**
	 * Array of the PayPal webhook event handlers. Add-ons can use the registerEventHandler method
	 * to add additional events/handlers.
	 *
	 * Structure: PayPalEventName => EventHandlerClass
	 *
	 * @since 2.8.0
	 *
	 * @var string[]
	 */
	private $eventHandlers = [];

	/**
	 * @var Webhooks
	 */
	private $webhooksRepository;

	/**
	 * @var MerchantDetails
	 */
	private $merchantRepository;

	/**
	 * PayPalWebhooks constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param Webhooks $webhooksRepository
	 */
	public function __construct( Webhooks $webhooksRepository, MerchantDetails $merchantRepository ) {
		$this->webhooksRepository = $webhooksRepository;
		$this->merchantRepository = $merchantRepository;
	}

	/**
	 * Use this to register additional events and handlers
	 *
	 * @since 2.8.0
	 *
	 * @param string $payPalEvent PayPal event to listen for, i.e. CHECKOUT.ORDER.APPROVED
	 * @param string $eventHandler The FQCN of the event handler
	 *
	 * @return $this
	 */
	public function registerEventHandler( $payPalEvent, $eventHandler ) {
		if ( isset( $this->eventHandlers[ $payPalEvent ] ) ) {
			throw new InvalidArgumentException( 'Cannot register an already registered event' );
		}

		if ( ! is_subclass_of( $eventHandler, EventListener::class ) ) {
			throw new InvalidArgumentException( 'Listener must be a subclass of ' . EventListener::class );
		}

		$this->eventHandlers[ $payPalEvent ] = $eventHandler;

		return $this;
	}

	/**
	 * Handles all webhook event requests. First it verifies that authenticity of the event with
	 * PayPal, and then it passes the event along to the appropriate listener to finish.
	 *
	 * @since 2.8.0
	 *
	 * @throws Exception
	 */
	public function handle() {
		if ( ! $this->merchantRepository->accountIsConnected() ) {
			return;
		}

		$merchantDetails = $this->merchantRepository->getDetails();

		$event = json_decode( file_get_contents( 'php://input' ), false );

		// If we receive an event that we're not expecting, just ignore it
		if ( ! isset( $this->eventHandlers[ $event->event_type ] ) ) {
			return;
		}

		if ( ! $this->webhooksRepository->verifyEventSignature( $merchantDetails->accessToken, $event, getallheaders() ) ) {
			throw new Exception( 'Failed event verification' );
		}

		/** @var EventListener $handler */
		$handler = give( $this->eventHandlers[ $event->event_type ] );

		$handler->processEvent( $event );
	}
}
