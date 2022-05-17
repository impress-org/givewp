<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;

use function Give\Framework\Http\Response\response;

class RedirectOffsiteHandler  {
    /**
     * @since 2.18.0
     */
    public function __invoke(RedirectOffsite $command): RedirectResponse
    {
        return response()->redirectTo($command->redirectUrl);
    }
}
