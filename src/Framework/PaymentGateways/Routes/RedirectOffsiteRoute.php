<?php

namespace Give\Framework\PaymentGateways\Routes;

/**
 * Class RedirectOffsiteRoute
 *
 * @unreleased
 */
class RedirectOffsiteRoute extends GatewayRoute {
	protected $gatewayMethod = 'handleReturnFromOffsiteRedirect';
}
