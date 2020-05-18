<?php

namespace Give\Request;

/**
 * Class Request
 *
 * @since 2.7.0
 * @package Give\Request
 */
class Request {
	/**
	 * $_GET parameter.
	 *
	 * @var ParameterBag
	 * @since 2.7.0
	 */
	public $query;

	/**
	 * $_POST parameter.
	 *
	 * @var ParameterBag
	 * @since 2.7.0
	 */
	public $request;

	/**
	 * $_COOKIE parameter.
	 *
	 * @var ParameterBag
	 * @since 2.7.0
	 */
	public $cookies;

	/**
	 * $_SERVER parameter.
	 *
	 * @var ServerBag
	 * @since 2.7.0
	 */
	public $server;

	/**
	 * Request constructor.
	 *
	 * @param  array $query  The GET parameters
	 * @param  array $request  The POST parameters
	 * @param  array $cookies  The COOKIE parameters
	 * @param  array $server  The SERVER parameters
	 * @since 2.7.0
	 */
	public function __construct( $query = [], $request = [], $cookies = [], $server = [] ) {
		$this->initialize( $query, $request, $cookies, $server );
	}

	/**
	 * Sets the parameters for this request.
	 *
	 * This method also re-initializes all properties.
	 *
	 * @param  array $query  The GET parameters
	 * @param  array $request  The POST parameters
	 * @param  array $cookies  The COOKIE parameters
	 * @param  array $server  The SERVER parameters
	 * @since 2.7.0
	 */
	public function initialize( $query = [], $request = [], $cookies = [], $server = [] ) {
		$this->request = new ParameterBag( $request );
		$this->query   = new ParameterBag( $query );
		$this->cookies = new ParameterBag( $cookies );
		$this->server  = new ServerBag( $server );
	}

	/**
	 * Get result whether or not performing Give core action on ajax or not.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public function isProcessingGiveActionOnAjax() {
		$action            = $this->query->get( 'action', '' );
		$whiteListedAction = [ 'get_receipt' ];

		$isGiveAction        = 0 === strpos( $action, 'give_' );
		$isWhiteListedAction = in_array( $action, $whiteListedAction, true );

		return $action && wp_doing_ajax() && ( $isGiveAction || $isWhiteListedAction );
	}

	/**
	 * Return if current URL loading in iframe or not.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public function inIframe() {
		return ! empty( $this->query->get( 'giveDonationFormInIframe', false ) );
	}
}
