<?php

namespace Give\Tests\Unit\PaymentGateways\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Actions\HandleGatewayPaymentCommand;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class HandleGatewayPaymentCommandTest extends TestCase {
    use RefreshDatabase;

    /**
     * @throws TypeNotSupported
     */
    public function testShouldHandlePaymentCompleteCommand()
    {
        $donation = Donation::factory()->create();

        $command = new PaymentComplete('gateway-transaction-id');

        $action = (new HandleGatewayPaymentCommand())($command, $donation);
        $response = new RedirectResponse(give_get_success_page_uri());

        $this->assertEquals($action, $response);
    }

    /**
     * @throws TypeNotSupported
     */
    public function testShouldHandlePaymentProcessingCommand()
    {
        $donation = Donation::factory()->create();

        $command = new PaymentProcessing();

        $action = (new HandleGatewayPaymentCommand())($command, $donation);
        $response = new RedirectResponse(give_get_success_page_uri());

        $this->assertEquals($action, $response);
    }

    /**
     * @throws TypeNotSupported
     */
    public function testShouldHandleRedirectOffsiteCommand()
    {
        $donation = Donation::factory()->create();

        $command = new RedirectOffsite('https://example.com');

        $action = (new HandleGatewayPaymentCommand())($command, $donation);
        $response = new RedirectResponse('https://example.com');

        $this->assertEquals($action, $response);
    }

    /**
     * @throws TypeNotSupported
     */
    public function testShouldHandleRespondToBrowserCommand()
    {
        $donation = Donation::factory()->create();

        $command = new RespondToBrowser(['data' => 'value']);

        $action = (new HandleGatewayPaymentCommand())($command, $donation);
        $response = new JsonResponse(['data' => 'value']);

        $this->assertEquals($action, $response);
    }

    /**
     * @throws TypeNotSupported
     */
    public function testShouldThrowTypeNotSupportedForInvalidCommand()
    {
        $this->expectException(TypeNotSupported::class);
        
        $donation = Donation::factory()->create();

        $command = new class implements GatewayCommand {};

        (new HandleGatewayPaymentCommand())($command, $donation);
    }
}