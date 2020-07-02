<?php

namespace Give\Vendor\PayPal\PayPalCheckoutSdk\Core;

use Give\Vendor\PayPal\PayPalHttp\Injector;
class GzipInjector implements \Give\Vendor\PayPal\PayPalHttp\Injector {

	public function inject( $httpRequest ) {
		$httpRequest->headers['Accept-Encoding'] = 'gzip';
	}
}
