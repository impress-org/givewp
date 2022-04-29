<?php

declare(strict_types=1);

use Give\Donations\LegacyListeners\DispatchGivePreInsertPayment;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;

/**
 * @coversDefaultClass DispatchGivePreInsertPayment
 */
class DispatchGivePreInsertPaymentTest extends Give_Unit_Test_Case
{
    /**
     * @unreleased
     */
    public function testShouldModifyDonationInsertionOnOldFilter()
    {
        add_filter('give_pre_insert_payment', static function(array $paymentData) {
            $paymentData['price'] = '35.25';
            $paymentData['currency'] = 'USD';
            $paymentData['formTitle'] = 'Another Form';
            $paymentData['formId'] = 123;
            $paymentData['purchaseKey'] = 'purchase-key';
            $paymentData['gateway'] = 'paypal';
            $paymentData['status'] = DonationStatus::PENDING;
            $paymentData['donor_id'] = 456;
            $paymentData['userInfo']['id'] = 789;
            $paymentData['userInfo']['firstName'] = 'Jack';
            $paymentData['userInfo']['lastName'] = 'Black';
            $paymentData['userInfo']['title'] = 'Mr.';
            $paymentData['userInfo']['email'] = 'jack.black@example.com';

            return $paymentData;
        }, 10, 2);

        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        self::assertSame('Another Form', $donation->formTitle);
        self::assertSame(123, $donation->formId);
        self::assertSame('purchase-key', $donation->purchaseKey);
        self::assertSame('paypal', $donation->gatewayId);
        self::assertEquals(DonationStatus::PENDING(), $donation->status);
    }
}
