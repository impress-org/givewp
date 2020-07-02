<?php

namespace Give\Vendor\Paypal\PayPalHttp\Serializer;

use Give\Vendor\Paypal\PayPalHttp\HttpRequest;
use Give\Vendor\Paypal\PayPalHttp\Serializer;
class Form implements \Give\Vendor\Paypal\PayPalHttp\Serializer {

	/**
	 * @return string Regex that matches the content type it supports.
	 */
	public function contentType() {
		return '/^application\\/x-www-form-urlencoded$/';
	}
	/**
	 * @param HttpRequest $request
	 * @return string representation of your data after being serialized.
	 */
	public function encode( \Give\Vendor\Paypal\PayPalHttp\HttpRequest $request ) {
		if ( ! \is_array( $request->body ) || ! $this->isAssociative( $request->body ) ) {
			throw new \Exception( 'HttpRequest body must be an associative array when Content-Type is: ' . $request->headers['Content-Type'] );
		}
		return \http_build_query( $request->body );
	}
	/**
	 * @param $body
	 * @return mixed
	 * @throws \Exception as multipart does not support deserialization.
	 */
	public function decode( $body ) {
		throw new \Exception( 'CurlSupported does not support deserialization' );
	}
	private function isAssociative( array $array ) {
		return \array_values( $array ) !== $array;
	}
}
