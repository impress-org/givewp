<?php

/**
 * Handle Embed Donation Form Route
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Route;

use Give\Controller\Form as Controller;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Template class.
 *
 * @since 2.7.0
 */
class Form {
	/**
	 * Option name
	 *
	 * @since 2.7.0
	 * @var string
	 */
	private $optionName = 'form_page_url_prefix';

	/**
	 * Route base
	 *
	 * @since 2.7.0
	 * @var string
	 */
	private $defaultBase = 'give';

	/**
	 * Route base
	 *
	 * @since 2.7.0
	 * @var string
	 */
	private $base;

	/**
	 * Form constructor.
	 *
	 * @param Controller $controller
	 */
	public function init( $controller ) {
		$this->setBasePrefix();
		$controller->init();

		add_action( 'init', [ $this, 'addRule' ] );
		add_action( 'query_vars', [ $this, 'addQueryVar' ] );
		add_action( 'give-settings_save_advanced', [ $this, 'updateRule' ], 11 );
	}

	/**
	 * Setup base prefix
	 *
	 * @since 2.7.0
	 */
	public function setBasePrefix() {
		$this->base = give_get_option( $this->optionName, $this->defaultBase );
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

	/**
	 * Get url base.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getOptionName() {
		return $this->optionName;
	}


	/**
	 * Update route rule
	 *
	 * @since 2.7.0
	 */
	public function updateRule() {
		global $wp_rewrite;

		$updateBase = give_get_option( $this->optionName, $this->defaultBase );

		if ( $updateBase !== $this->base ) {
			$this->base = $updateBase;

			// Save rewrite rule manually.
			$this->addRule();
			flush_rewrite_rules();
			$wp_rewrite->wp_rewrite_rules();
		}
	}

	/**
	 * Get queried form ID.
	 *
	 * @since 2.7.0
	 * @return int
	 */
	public function getQueriedFormID() {
		$formId = 0;

		if ( $queryVar = get_query_var( 'give_form_id' ) ) {
			$form = current(
				get_posts(
					[
						'name'        => $queryVar,
						'numberposts' => 1,
						'post_type'   => 'give_forms',
					]
				)
			);

			if ( $form instanceof WP_Post ) {
				$formId = $form->ID;
			}
		}

		return $formId;
	}
}
