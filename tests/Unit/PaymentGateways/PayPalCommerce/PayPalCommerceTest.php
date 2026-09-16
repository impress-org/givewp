<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalCommerce;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
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
     * Both form versions send an approved order and let the gateway capture it, so an order that is
     * already captured cannot be paying for this donation.
     *
     * @since TBD
     */
    public function testAlreadyCapturedOrderIsRejected(): void
    {
        $donation = $this->createDonation('25.00');

        $order = $this->capturedOrder('CAPTURE1', '25.00', 'USD');
        $order->status = 'COMPLETED';

        $this->payPalOrder->method('getApprovedOrder')->willReturn($order);
        $this->payPalOrder->expects($this->never())->method('updateOrderFromDonation');
        $this->payPalOrder->expects($this->never())->method('approveOrder');

        $this->expectException(PaymentGatewayException::class);

        give(PayPalCommerce::class)->createPayment($donation, ['payPalOrderId' => 'ORDER123']);
    }

    /**
     * @since TBD
     */
    public function testCapturedAmountNotMatchingTheDonationIsRejected(): void
    {
        $donation = $this->createDonation('25.00');

        $this->payPalOrder->method('getApprovedOrder')->willReturn($this->approvedOrder('25.00', 'USD'));
        $this->payPalOrder->method('approveOrder')->willReturn($this->capturedOrder('CAPTURE1', '1.00', 'USD'));

        $this->expectException(PaymentGatewayException::class);

        give(PayPalCommerce::class)->createPayment($donation, ['payPalOrderId' => 'ORDER123']);
    }

    /**
     * An invalid CVV is reported as a failed capture carrying a processor response, which used to be
     * read by the ajax endpoint that no longer captures.
     *
     * @since TBD
     */
    public function testFailedCaptureIsRejectedWithItsProcessorResponse(): void
    {
        $donation = $this->createDonation('25.00');

        $capturedOrder = $this->capturedOrder('CAPTURE1');
        $capture = $capturedOrder->purchase_units[0]->payments->captures[0];
        $capture->status = 'FAILED';
        $capture->processor_response = (object)['cvv_code' => 'N'];

        $this->payPalOrder->method('getApprovedOrder')->willReturn($this->approvedOrder('25.00', 'USD'));
        $this->payPalOrder->method('approveOrder')->willReturn($capturedOrder);

        $this->expectException(PaymentGatewayException::class);
        /* The message has to be the CVV one, not the generic decline the fallback would supply. */
        $this->expectExceptionMessage('the CVV2/CSC does not match');

        give(PayPalCommerce::class)->createPayment($donation, ['payPalOrderId' => 'ORDER123']);
    }

    /**
     * @since TBD Drop the $gatewayTransactionId param along with the capture-reuse check it fed.
     * @since 4.16.8.1 Add the optional $gatewayTransactionId param.
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
     * @since TBD Give the capture its own amount, which is what the gateway validates.
     * @since 4.16.8.1 Add the $amount/$currency params.
     * @since 4.16.7.1
     */
    private function capturedOrder(string $captureId, string $amount = '25.00', string $currency = 'USD'): stdClass
    {
        $capture = (object)[
            'id' => $captureId,
            'status' => 'COMPLETED',
            'amount' => (object)['value' => $amount, 'currency_code' => $currency],
        ];

        $order = new stdClass();
        $order->id = 'ORDER123';
        $order->status = 'COMPLETED';
        $order->purchase_units = [(object)[
            'amount' => (object)['value' => $amount, 'currency_code' => $currency],
            'payments' => (object)['captures' => [$capture]],
        ]];

        return $order;
    }
}
