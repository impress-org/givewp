<?php

namespace Give\Tests\Unit\PaymentGateways\Actions;

use Exception;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Actions\HandleGatewaySubscriptionCommand;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Framework\PaymentGateways\Commands\SubscriptionProcessing;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class HandleGatewaySubscriptionCommandTest extends TestCase {
    use RefreshDatabase;

    /**
     * @since 2.27.0
     * @throws TypeNotSupported|Exception
     */
    public function testShouldHandleSubscriptionCompleteCommand()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $command = new SubscriptionComplete('gateway-transaction-id', 'gateway-subscription-id');

        $action = (new HandleGatewaySubscriptionCommand())($command, $donation, $subscription);
        $response = new RedirectResponse(give_get_success_page_uri());

        $this->assertEquals($action, $response);
    }

    /**
     * @since 2.27.0
     * @throws TypeNotSupported|Exception
     */
    public function testShouldHandleSubscriptionProcessingCommand()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $command = new SubscriptionProcessing('gateway-subscription-id', 'gateway-transaction-id');

        $action = (new HandleGatewaySubscriptionCommand())($command, $donation, $subscription);
        $response = new RedirectResponse(give_get_success_page_uri());

        $this->assertEquals($action, $response);
    }

    /**
     * @since 2.27.0
     * @throws TypeNotSupported|Exception
     */
    public function testShouldHandleRedirectOffsiteCommand()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $command = new RedirectOffsite('https://example.com');

        $action = (new HandleGatewaySubscriptionCommand())($command, $donation, $subscription);
        $response = new RedirectResponse('https://example.com');

        $this->assertEquals($action, $response);
    }

    /**
     * @since 2.27.0
     * @throws TypeNotSupported|Exception
     */
    public function testShouldHandleRespondToBrowserCommand()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $command = new RespondToBrowser(['data' => 'value']);

        $action = (new HandleGatewaySubscriptionCommand())($command, $donation, $subscription);
        $response = new JsonResponse(['data' => 'value']);

        $this->assertEquals($action, $response);
    }

    /**
     * @since 2.27.0
     * @throws TypeNotSupported|Exception
     */
    public function testShouldThrowTypeNotSupportedForInvalidCommand()
    {
        $this->expectException(TypeNotSupported::class);

        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $command = new class implements GatewayCommand {};

        (new HandleGatewaySubscriptionCommand())($command, $donation, $subscription);
    }
}
