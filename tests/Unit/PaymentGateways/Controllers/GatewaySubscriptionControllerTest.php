<?php

namespace Give\Tests\Unit\PaymentGateways\Controllers;

use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Controllers\GatewaySubscriptionController;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;

class GatewaySubscriptionControllerTest extends TestCase {
    use RefreshDatabase;

    /**
     * @since 2.27.0
     */
    public function testShouldCallGatewayCreateSubscription(){
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
        ]);

        $donation = $subscription->initialDonation();

        $mockGateway = $this->getMockGateway();

        $mockCommand = new PaymentComplete('mock-transaction-id');

         /** @var PHPUnit_Framework_MockObject_MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('createSubscription')
            ->with($donation, $subscription)
            ->willReturn($mockCommand);

        $mockController = $this->getMockController();
        $controller = new $mockController($mockGateway);
        $controller->create($donation, $subscription);
    }

    /**
     * @since 2.27.0
     */
    protected function getMockGateway()
    {
        return $this->createMock(
            TestGateway::class,
            function (PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['createSubscription']);

                return $mockBuilder->getMock();
            }
        );
    }

    /**
     * @since 2.27.0
     */
    protected function getMockController()
    {
        return $this->createMock(
            GatewaySubscriptionController::class,
            function (PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['handleGatewayCommand']);

                return $mockBuilder->getMock();
            }
        );
    }

}
