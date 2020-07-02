<?php

namespace Give\Vendor\Paypal\PayPalCheckoutSdk\Core;

use Give\Vendor\Paypal\PayPalHttp\Injector;
class GzipInjector implements \Give\Vendor\Paypal\PayPalHttp\Injector {

	public function inject( $httpRequest ) {
		$httpRequest->headers['Accept-Encoding'] = 'gzip';
	}
}
