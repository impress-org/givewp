<?php
namespace Give\Request;

use WP;

/**
 * Class Request
 *
 * @package Give\Request
 */
class Request {
	/**
	 * @var WP
	 */
	private $wp;

	/**
	 * Request constructor.
	 *
	 * @param WP $wpRequest
	 */
	public function __construct( $wpRequest ) {
		$this->wp = $wpRequest;
	}

	/**
	 * Setup request.
	 */
	public function init() {
		error_log( print_r( $this->wp, true ) . "\n", 3, WP_CONTENT_DIR . '/debug_new.log' );
	}
}
