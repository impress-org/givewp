<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;

use function Give\Framework\Http\Response\response;

class RedirectOffsiteHandler  {
    /**
     * @since 2.18.0
     *
     * @param  RedirectOffsite  $command
     * @return RedirectResponse
     */
    public function __invoke(RedirectOffsite $command)
    {
        return response()->redirectTo($command->redirectUrl);
    }
}
