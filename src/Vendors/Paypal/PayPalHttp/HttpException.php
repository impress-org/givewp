<?php

namespace Give\Vendor\PayPal\PayPalHttp;

class HttpException extends \Give\Vendor\PayPal\PayPalHttp\IOException {

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
