<?php

namespace Give\Vendor\PayPal\PayPalHttp;

/**
 * Interface Injector
 * @package PayPalHttp
 *
 * Interface that can be implemented to apply injectors to Http client.
 *
 * @see HttpClient
 */
interface Injector {

	/**
	 * @param $httpRequest HttpRequest
	 */
	public function inject( $httpRequest);
}
