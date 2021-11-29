<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\Support\Facades\Facade;

/**
 * @unreleased
 *
 * @method static get(string $gatewayMethod): $this
 * @method static offsite(): $this
 * @method static onsite(): $this
 */
class GatewayRoute extends Facade
{
    protected function getFacadeAccessor()
    {
        return GatewayRouteFacade::class;
    }
}
