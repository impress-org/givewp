<?php

/**
 * Handle Embed Donation Form Route
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Route;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
class Form {
	/**
	 * Route base
	 *
	 * @since 2.7.0
	 * @var string
	 */
	private $base = 'give';

	/**
	 * Initialize
	 *
	 * @since 2.7.0
	 */
	public function init() {
		global $wp;

		// Add query var and rewrite rule.
		$wp->add_query_var( 'give_form_id' );
		add_rewrite_rule(
			"{$this->base}/([a-z]+)/?$",
			'index.php?name=give-embed&give_form_id=$matches[1]',
			'top'
		);
	}

	/**
	 * Get form URL.
	 *
	 * @since 2.7.0
	 * @param int $form_id
	 *
	 * @return string
	 */
	public function getURL( $form_id ) {
		return home_url( "/{$this->base}/{$form_id}" );
	}
}
