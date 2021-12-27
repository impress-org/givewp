<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;

use function Give\Framework\Http\Response\response;

class RespondToBrowserHandler  {
    /**
     * @unreleased
     *
     * @param  RespondToBrowser $command
     * @return JsonResponse
     */
    public function __invoke(RespondToBrowser $command)
    {
        return response()->json($command->data);
    }
}