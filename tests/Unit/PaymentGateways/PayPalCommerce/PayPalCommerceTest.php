<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalCommerce;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * createPayment() is where PayPal funds are captured, and it is the reason the client-supplied
 * order amount can be trusted nowhere else: the order is re-fetched from PayPal and patched to the
 * validated donation amount before capture.
 *
 * @since 4.16.7.1
 *
 * @covers \Give\PaymentGateways\PayPalCommerce\PayPalCommerce::createPayment
 */
class PayPalCommerceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PayPalOrder|MockObject
     */
    private $payPalOrder;

    /**
     * @since 4.16.7.1
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->payPalOrder = $this->createMock(PayPalOrder::class);
        give()->instance(PayPalOrder::class, $this->payPalOrder);
    }

    /**
     * @since 4.16.7.1
     */
    public function tearDown(): void
    {
        give()->forgetInstance(PayPalOrder::class);

        parent::tearDown();
    }

    /**
     * @since 4.16.7.1
     */
    public function testApprovedOrderWithDifferentAmountIsPatchedToDonationAmountBeforeCapture(): void
    {
        $donation = $this->createDonation('25.00');

        $this->payPalOrder->method('getApprovedOrder')
            ->with('ORDER123')
            ->willReturn($this->approvedOrder('999999.00', 'USD'));

        $this->payPalOrder->expects($this->once())
            ->method('updateOrderFromDonation')
            ->with('ORDER123', $this->callback(static function (Donation $patched) use ($donation) {
                return $patched->id === $donation->id;
            }));

        $this->payPalOrder->expects($this->once())
            ->method('approveOrder')
            ->with('ORDER123')
            ->willReturn($this->capturedOrder('CAPTURE1'));

        $command = give(PayPalCommerce::class)->createPayment($donation, ['payPalOrderId' => 'ORDER123']);

        $this->assertInstanceOf(PaymentComplete::class, $command);
        $this->assertSame('CAPTURE1', $command->gatewayTransactionId);
        $this->assertSame('ORDER123', give()->payment_meta->get_meta($donation->id, '_give_order_id', true));
    }

    /**
     * @since 4.16.7.1
     */
    public function testApprovedOrderWithMatchingAmountIsCapturedWithoutPatching(): void
    {
        $donation = $this->createDonation('25.00');

        $this->payPalOrder->method('getApprovedOrder')
            ->willReturn($this->approvedOrder('25.00', 'USD'));

        $this->payPalOrder->expects($this->never())->method('updateOrderFromDonation');

        $this->payPalOrder->expects($this->once())
            ->method('approveOrder')
            ->with('ORDER123')
            ->willReturn($this->capturedOrder('CAPTURE1'));

        $command = give(PayPalCommerce::class)->createPayment($donation, ['payPalOrderId' => 'ORDER123']);

        $this->assertSame('CAPTURE1', $command->gatewayTransactionId);
    }

    /**
     * An order the v2 ajax endpoint already captured is recorded as-is; nothing is patched or
     * captured again.
     *
     * @since 4.16.7.1
     */
    public function testCompletedOrderIsRecordedWithoutCapturingAgain(): void
    {
        $donation = $this->createDonation('25.00');

        $order = $this->capturedOrder('CAPTURE1');
        $order->status = 'COMPLETED';

        $this->payPalOrder->method('getApprovedOrder')->willReturn($order);
        $this->payPalOrder->expects($this->never())->method('updateOrderFromDonation');
        $this->payPalOrder->expects($this->never())->method('approveOrder');

        $command = give(PayPalCommerce::class)->createPayment($donation, ['payPalOrderId' => 'ORDER123']);

        $this->assertSame('CAPTURE1', $command->gatewayTransactionId);
    }

    /**
     * @since 4.16.7.1
     */
    private function createDonation(string $amount): Donation
    {
        return Donation::factory()->create([
            'gatewayId' => PayPalCommerce::id(),
            'amount' => Money::fromDecimal($amount, 'USD'),
        ]);
    }

    /**
     * The shape PayPal returns from GET /v2/checkout/orders for an order the donor has approved
     * but nobody has captured.
     *
     * @since 4.16.7.1
     */
    private function approvedOrder(string $amount, string $currency): stdClass
    {
        $order = new stdClass();
        $order->id = 'ORDER123';
        $order->status = 'APPROVED';
        $order->purchase_units = [(object)['amount' => (object)['value' => $amount, 'currency_code' => $currency]]];

        return $order;
    }

    /**
     * The shape PayPal returns from POST /v2/checkout/orders/{id}/capture.
     *
     * @since 4.16.7.1
     */
    private function capturedOrder(string $captureId): stdClass
    {
        $capture = (object)['id' => $captureId, 'status' => 'COMPLETED'];

        $order = new stdClass();
        $order->id = 'ORDER123';
        $order->status = 'COMPLETED';
        $order->purchase_units = [(object)['payments' => (object)['captures' => [$capture]]]];

        return $order;
    }
}
