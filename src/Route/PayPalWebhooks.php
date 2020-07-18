<?php

namespace Give\Route;

use Give\Controller\PayPalWebhooks as Controller;

class PayPalWebhooks implements Route {
	/**
	 * @inheritDoc
	 */
	public function init() {
		add_action( 'init', [ $this, 'addRule' ] );
		add_action( 'query_vars', [ $this, 'addQueryVars' ] );
		add_action( 'wp', [ $this, 'callController' ] );
	}

	/**
	 * Adds the rewrite rules to WordPress
	 *
	 * @since 2.8.0
	 */
	public function addRule() {
		add_rewrite_rule(
			'^give/paypal/commerce/webhook$',
			'index.php?give_webhook_event=paypal-commerce',
			'top'
		);
	}

	/**
	 * Adds the rewrite rules to WordPress
	 *
	 * @since 2.8.0
	 *
	 * @param string[] $vars
	 *
	 * @return string[]
	 */
	public function addQueryVars( $vars ) {
		$vars[] = 'give_webhook_event';

		return $vars;
	}

	/**
	 * Calls the corresponding controller for the route in the appropriate context
	 *
	 * @since 2.8.0
	 */
	public function callController() {
		if ( get_query_var( 'give_webhook_event', null ) ) {
			give( Controller::class )->handle();

			http_response_code( 200 );
			die();
		}
	}
}
