<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Recurring Gateway Factory Class
 *
 * The Give recurring gateway factory creating the right gateway object.
 *
 * @class    Give_Recurring_Gateway_Factory
 */
class Give_Recurring_Gateway_Factory {

	/**
	 * Get gateway object based on subscription
	 *
	 * @param  Give_Subscription|int $subscription Give_Subscription object or subscription_id
	 *
	 * @return Give_Recurring_Gateway|boolean
	 */
	public function get_gateway_from_subscription( $subscription ) {

		$gateway_id = '';

		if ( $subscription instanceof Give_Subscription ) {

			$gateway_id = $subscription->gateway;

		} elseif ( is_numeric( $subscription ) ) {

			$subscription = new Give_Subscription( $subscription );
			$gateway_id   = $subscription->gateway;

		}

		return $this->get_gateway( $gateway_id, $subscription );
	}

	/**
	 * Get gateway object based on gateway_id
	 *
	 * @param  string $gateway_id
	 * @param  \Give_Subscription $subscription
	 *
	 * @return Give_Recurring_Gateway|boolean
	 */
	public function get_gateway( $gateway_id, $subscription ) {
		$class_name = 'Give_Recurring_' . ucfirst( $gateway_id );

		if ( class_exists( $class_name ) ) {
			$ret = new $class_name();
		} else {
			$ret = false;
		}

		return apply_filters( 'give_recurring_gateway_factory_get_gateway', $ret, $gateway_id, $subscription );
	}
}


/**
 * Main function for returning gateway from subscription, uses the Give_Recurring_Gateway_Factory class
 *
 * @param  Give_Subscription|int $subscription subscription id or Give_Subscription object
 *
 * @return Give_Recurring_Gateway
 */
function give_recurring_get_gateway_from_subscription( $subscription ) {
	return Give_Recurring()->gateway_factory->get_gateway_from_subscription( $subscription );
}

/**
 * Main function for returning gateway, uses the Give_Recurring_Gateway_Factory class
 *
 * @param  string $gateway_id
 * @param   $subscription
 *
 * @return Give_Recurring_Gateway
 */
function give_recurring_get_gateway( $gateway_id, $subscription ) {
	return Give_Recurring()->gateway_factory->get_gateway( $gateway_id, $subscription );
}
