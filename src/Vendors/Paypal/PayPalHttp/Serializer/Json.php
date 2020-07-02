<?php

namespace Give\Vendor\Paypal\PayPalHttp\Serializer;

use Give\Vendor\Paypal\PayPalHttp\HttpRequest;
use Give\Vendor\Paypal\PayPalHttp\Serializer;
/**
 * Class Json
 * @package PayPalHttp\Serializer
 *
 * Serializer for JSON content types.
 */
class Json implements \Give\Vendor\Paypal\PayPalHttp\Serializer {

	public function contentType() {
		return '/^application\\/json/';
	}
	public function encode( \Give\Vendor\Paypal\PayPalHttp\HttpRequest $request ) {
		$body = $request->body;
		if ( \is_string( $body ) ) {
			return $body;
		}
		if ( \is_array( $body ) ) {
			return \json_encode( $body );
		}
		throw new \Exception( 'Cannot serialize data. Unknown type' );
	}
	public function decode( $data ) {
		return \json_decode( $data );
	}
}
