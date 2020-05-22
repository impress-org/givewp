<?php

namespace Give\Request;

use Give\Helpers\Form\Template\Utils\Frontend;

/**
 * Class Request
 *
 * Note: This API is still in development and we are using this for internal purpose. we will not recommend it to use publically.
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
	 *
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
	 *
	 * @since 2.7.0
	 */
	public function initialize( $query = [], $request = [], $cookies = [], $server = [] ) {
		$this->request = new ParameterBag( $request );
		$this->query   = new ParameterBag( $query );
		$this->cookies = new ParameterBag( $cookies );
		$this->server  = new ServerBag( $server );
	}

	/**
	 * Returns a parameter by name from $_POST or $_GET
	 *
	 * @param  string $key
	 * @param  mixed  $default  The default value if the parameter key does not exist
	 *
	 * @return mixed
	 * @since 2.7.0
	 */
	public function get( $key, $default = null ) {
		$value = $default;

		if ( $this->request->has( $key ) ) {
			$value = $this->request->get( $key, $default );

		} elseif ( $this->query->has( $key ) ) {
			$value = $this->query->get( $key, $default );
		}

		return $value;
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
	 * Get result whether or not donation form in iframe.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public function isDonationFormInIframe() {
		return ! empty( $this->query->get( 'giveDonationFormInIframe', false ) );
	}

	/**
	 * Get result if we are processing embed form or not
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public function isProcessingForm() {
		$base     = Give()->routeForm->getBase();
		$formName = get_post_field( 'post_name', Frontend::getFormId() );
		$referer  = trailingslashit( wp_get_referer() );

		return ! empty( $_REQUEST['give_embed_form'] ) ||
			   false !== strpos( trailingslashit( wp_get_referer() ), "/{$base}/{$formName}/" ) ||
			   $this->isDonationFormInIframe() ||
			   false !== strpos( $referer, 'giveDonationFormInIframe' );
	}
}
