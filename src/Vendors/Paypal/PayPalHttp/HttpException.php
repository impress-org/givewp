<?php

namespace Give\Vendor\Paypal\PayPalHttp;

class HttpException extends \Give\Vendor\Paypal\PayPalHttp\IOException {

	/**
	 * @var statusCode
	 */
	public $statusCode;
	public $headers;
	/**
	 * @param string $response
	 */
	public function __construct( $message, $statusCode, $headers ) {
		parent::__construct( $message );
		$this->statusCode = $statusCode;
		$this->headers    = $headers;
	}
}
