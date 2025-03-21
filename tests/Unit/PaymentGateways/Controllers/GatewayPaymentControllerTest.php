<?php

namespace Give\Tests\Unit\PaymentGateways\Controllers;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Controllers\GatewayPaymentController;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class GatewayPaymentControllerTest extends TestCase {
    use RefreshDatabase;

    /**
     * @since 2.27.0
     */
    public function testShouldCallGatewayCreatePayment(){
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => TestGateway::id(),
        ]);

        $mockGateway = $this->getMockGateway();

        $mockCommand = new PaymentComplete('mock-transaction-id');

        /** @var MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('createPayment')
            ->with($donation)
            ->willReturn($mockCommand);

        $mockController = $this->getMockController();
        $controller = new $mockController($mockGateway);
        $controller->create($donation);
    }

    /**
     * @since 2.29.0
     */
    public function testShouldCallGatewayRefundDonation()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => TestGateway::id(),
        ]);

        $mockGateway = $this->getMockGateway();

        $mockCommand = new PaymentRefunded('mock-transaction-id');

        /** @var MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('refundDonation')
            ->with($donation)
            ->willReturn($mockCommand);

        $mockController = $this->getMockController();
        $controller = new $mockController($mockGateway);
        $controller->refund($donation);
    }

    /**
     * @since 2.27.0
     */
    protected function getMockGateway()
    {
        return $this->createMockWithCallback(
            TestGateway::class,
            function (MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['createPayment', 'refundDonation']);

                return $mockBuilder->getMock();
            }
        );
    }

     /**
     * @since 2.27.0
     */
    protected function getMockController()
    {
        return $this->createMockWithCallback(
            GatewayPaymentController::class,
            function (MockBuilder $mockBuilder) {
                // partial mock gateway by setting methods on the mock builder
                $mockBuilder->setMethods(['handleGatewayCommand']);

                return $mockBuilder->getMock();
            }
        );
    }
}
