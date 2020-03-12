<?php

/**
 * Handle Embed Donation Form Route
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Route;

use Give\Controller\Form as Controller;

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
	 * Form constructor.
	 *
	 * @param Controller $controller
	 */
	public function __construct( $controller ) {
		$controller->init();

		add_action( 'query_vars', array( $this, 'addQueryVar' ) );
	}


	/**
	 * Add rewrite rule
	 *
	 * @since 2.7.0
	 */
	public function addRule() {
		add_rewrite_rule(
			"{$this->base}/(.+?)/?$",
			sprintf(
				'index.php?name=%1$s&give_form_id=$matches[1]',
				$this->base
			),
			'top'
		);
	}


	/**
	 * Add query var
	 *
	 * @since 2.7.0
	 * @param array $queryVars
	 *
	 * @return array
	 */
	public function addQueryVar( $queryVars ) {
		$queryVars[] = 'give_form_id';

		return $queryVars;
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


	/**
	 * Get url base.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}
}
