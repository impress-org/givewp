<?php

namespace TestsNextGen\Feature\Gateways;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\NextGen\Gateways\NextGenTestGateway\NextGenTestGateway;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

class NextGenTestGatewayTest extends TestCase
{
    use RefreshDatabase;

     public function testShouldCreatePaymentAndReturnRespondToBrowser()
    {
        $gateway = new NextGenTestGateway();
        $donation = Donation::factory()->create();
        $gatewayData = ['testGatewayIntent' => 'test-gateway-intent'];

        $response = $gateway->createPayment($donation, $gatewayData);

        $command = new RespondToBrowser([
            'donation' => $donation->toArray(),
            'redirectUrl' => give_get_success_page_uri(),
            'intent' => $gatewayData['testGatewayIntent']
        ]);

        $this->assertInstanceOf(RespondToBrowser::class, $response);
        $this->assertSame($command->data, $response->data);
    }
}
