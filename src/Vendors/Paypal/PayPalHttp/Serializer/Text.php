<?php

namespace Give\Vendor\Paypal\PayPalHttp\Serializer;

use Give\Vendor\Paypal\PayPalHttp\HttpRequest;
use Give\Vendor\Paypal\PayPalHttp\Serializer;
/**
 * Class Text
 * @package PayPalHttp\Serializer
 *
 * Serializer for Text content types.
 */
class Text implements \Give\Vendor\Paypal\PayPalHttp\Serializer {

	public function contentType() {
		return '/^text\\/.*/';
	}
	public function encode( \Give\Vendor\Paypal\PayPalHttp\HttpRequest $request ) {
		$body = $request->body;
		if ( \is_string( $body ) ) {
			return $body;
		}
		if ( \is_array( $body ) ) {
			return \json_encode( $body );
		}
		return \implode( ' ', $body );
	}
	public function decode( $data ) {
		return $data;
	}
}
