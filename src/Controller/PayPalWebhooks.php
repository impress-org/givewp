<?php

namespace Give\Controller;

class PayPalWebhooks {
	public function handle() {
		// $event->event_type
		$event = json_decode( file_get_contents( 'php://input' ), false );

		$handlerClass = 'Give\\PaymentGateways\\PayPalCommerce\\Webhooks\\Listeners\\'
						. $this->deriveHandlerClass( $event->event_type );

		/**
		 *
		 */
		$handler = new $handlerClass();
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
