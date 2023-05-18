<?php

namespace Give\Tests\Unit\PaymentGateways\CommandHandlers;

use Exception;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\CommandHandlers\RespondToBrowserHandler;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class RespondToBrowserTest extends TestCase {
    use RefreshDatabase;

    /**
     * @since 2.27.0
     * @throws Exception
     */
    public function testShouldHandleRespondToBrowserCommand()
    {
        $command = new RespondToBrowser(['data' => 'value']);

        $response = (new RespondToBrowserHandler())($command);

        $this->assertEquals($response, new JsonResponse(['data' => 'value']));
    }
}
