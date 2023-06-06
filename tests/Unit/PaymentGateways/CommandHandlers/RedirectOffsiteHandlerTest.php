<?php

namespace Give\Tests\Unit\PaymentGateways\CommandHandlers;

use Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\CommandHandlers\RedirectOffsiteHandler;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class RedirectOffsiteHandlerTest extends TestCase {
    use RefreshDatabase;

    /**
     * @since 2.27.0
     * @throws Exception
     */
    public function testShouldHandleRedirectOffsiteCommand()
    {
        $command = new RedirectOffsite('https://example.com');

        $response = (new RedirectOffsiteHandler())($command);

        $this->assertEquals($response, new RedirectResponse('https://example.com'));
    }
}
