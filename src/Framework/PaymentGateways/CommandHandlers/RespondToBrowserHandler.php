<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;

use function Give\Framework\Http\Response\response;

class RespondToBrowserHandler  {
    /**
     * @since 2.18.0
     */
    public function __invoke(RespondToBrowser $command): JsonResponse
    {
        return response()->json($command->data);
    }
}
