<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;

use function Give\Framework\Http\Response\response;

class RespondToBrowserHandler  {
    /**
     * @since 2.18.0
     *
     * @param  RespondToBrowser $command
     * @return JsonResponse
     */
    public function __invoke(RespondToBrowser $command)
    {
        return response()->json($command->data);
    }
}
