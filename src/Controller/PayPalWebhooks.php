<?php

namespace Give\Controller;

use Exception;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\EventListener;

class PayPalWebhooks {
	/**
	 * @var Webhooks
	 */
	private $webhooksRepository;

	/**
	 * PayPalWebhooks constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param Webhooks $webhooksRepository
	 */
	public function __construct( Webhooks $webhooksRepository ) {
		$this->webhooksRepository = $webhooksRepository;
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
		$event = json_decode( file_get_contents( 'php://input' ), false );

		if ( ! $this->webhooksRepository->verifyEventSignature( $event, getallheaders() ) ) {
			throw new Exception( 'Failed event verification' );
		}

		$handlerClass = 'Give\\PaymentGateways\\PayPalCommerce\\Webhooks\\Listeners\\'
						. $this->deriveHandlerClass( $event->event_type );

		/** @var EventListener $handler */
		$handler = new $handlerClass();

		$handler->processEvent( $event );
	}

	/**
	 * This takes an event type such as CHECKOUT.ORDER.APPROVED, breaks it apart, and puts it back
	 * together as a studly cased class: CheckoutOrderApproved.
	 *
	 * @since 2.8.0
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
