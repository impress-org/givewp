<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\StripePaymentElementGateway;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\Refund;

class StripePaymentElementGatewayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testRefundDonationShouldCallSetUpStripeAppInfo()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => StripePaymentElementGateway::id(),
            'status' => DonationStatus::COMPLETE(),
        ]);

        $mockRefund = Refund::constructFrom(['status' => 'succeeded']);

        $mockGateway = $this->getMockGateway();

        /** @var MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('setUpStripeAppInfo')
            ->with($donation->formId);

        /** @var MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('refundStripePayment')
            ->willReturn($mockRefund);

        $result = $mockGateway->refundDonation($donation);

        $this->assertInstanceOf(PaymentRefunded::class, $result);
    }

    /**
     * @since TBD
     */
    public function testRefundDonationShouldCallRefundStripePayment()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => StripePaymentElementGateway::id(),
            'status' => DonationStatus::COMPLETE(),
        ]);

        $mockRefund = Refund::constructFrom(['status' => 'succeeded']);

        $mockGateway = $this->getMockGateway();

        /** @var MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('refundStripePayment')
            ->with($donation, $this->anything())
            ->willReturn($mockRefund);

        $result = $mockGateway->refundDonation($donation);

        $this->assertInstanceOf(PaymentRefunded::class, $result);
    }

    /**
     * @since TBD
     */
    public function testRefundDonationShouldThrowExceptionWhenStripeApiFails()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => StripePaymentElementGateway::id(),
            'status' => DonationStatus::COMPLETE(),
        ]);

        $mockGateway = $this->getMockGateway();

        /** @var MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('refundStripePayment')
            ->willThrowException(new Exception('Stripe API error'));

        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('Stripe API error: Stripe API error');

        $mockGateway->refundDonation($donation);
    }

    /**
     * @since TBD
     */
    public function testRefundDonationShouldCreateDonationNoteOnSuccess()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => StripePaymentElementGateway::id(),
            'status' => DonationStatus::COMPLETE(),
            'gatewayTransactionId' => 'stripe-test-transaction-id',
        ]);

        $mockRefund = Refund::constructFrom(['status' => 'succeeded']);

        $mockGateway = $this->getMockGateway();

        /** @var MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('refundStripePayment')
            ->willReturn($mockRefund);

        $mockGateway->refundDonation($donation);

        $donation = Donation::find($donation->id);
        $notes = $donation->notes()->getAll();

        $this->assertCount(1, $notes);
        $this->assertStringContainsString('Donation refunded in Stripe', $notes[0]->content);
    }

    /**
     * @since TBD
     */
    public function testRefundDonationShouldCreateDonationNoteOnFailure()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => StripePaymentElementGateway::id(),
            'status' => DonationStatus::COMPLETE(),
        ]);

        $mockGateway = $this->getMockGateway();

        /** @var MockObject $mockGateway */
        $mockGateway->expects($this->once())
            ->method('refundStripePayment')
            ->willThrowException(new Exception('Stripe API error'));

        try {
            $mockGateway->refundDonation($donation);
        } catch (PaymentGatewayException $e) {
            // Expected exception
        }

        $donation = Donation::find($donation->id);
        $notes = $donation->notes()->getAll();

        $this->assertCount(1, $notes);
        $this->assertStringContainsString('NOT refunded', $notes[0]->content);
    }

    /**
     * @since TBD
     */
    protected function getMockGateway(array $methods = []): MockObject
    {
        return $this->createMockWithCallback(
            StripePaymentElementGateway::class,
            function (MockBuilder $mockBuilder) use ($methods) {
                $mockBuilder->setMethods(
                    array_merge(['refundStripePayment', 'setUpStripeAppInfo'], $methods)
                );

                return $mockBuilder->getMock();
            }
        );
    }
}
