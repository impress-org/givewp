<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Donations\LegacyListeners;

use Give\Donations\LegacyListeners\DispatchGivePreInsertPayment;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;

/**
 * @coversDefaultClass DispatchGivePreInsertPayment
 */
class DispatchGivePreInsertPaymentTest extends TestCase
{
    /**
     * @since 2.19.6
     */
    public function testShouldModifyDonationInsertionOnOldFilter()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();
        $donor->email = 'jack.black@example.com';

        add_filter('give_pre_insert_payment', static function(array $paymentData) use ($donor) {
            $paymentData['price'] = '35.25';
            $paymentData['currency'] = 'USD';
            $paymentData['formTitle'] = 'Another Form';
            $paymentData['formId'] = 123;
            $paymentData['purchaseKey'] = 'purchase-key';
            $paymentData['gateway'] = 'paypal';
            $paymentData['status'] = DonationStatus::PENDING;
            $paymentData['donor_id'] = $donor->id;
            $paymentData['userInfo']['id'] = $donor->userId;
            $paymentData['userInfo']['firstName'] = $donor->firstName;
            $paymentData['userInfo']['lastName'] = $donor->lastName;
            $paymentData['userInfo']['title'] = $donor->prefix;
            $paymentData['userInfo']['email'] = $donor->email;

            return $paymentData;
        }, 10, 2);

        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        self::assertSame('Another Form', $donation->formTitle);
        self::assertSame(123, $donation->formId);
        self::assertSame('purchase-key', $donation->purchaseKey);
        self::assertSame('paypal', $donation->gatewayId);
        self::assertEquals(DonationStatus::PENDING(), $donation->status);

        self::assertSame($donor->id, $donation->donorId);
        self::assertSame($donor->userId, $donation->donor->userId);
        self::assertSame($donor->firstName, $donation->donor->firstName);
        self::assertSame($donor->lastName, $donation->donor->lastName);
        self::assertSame($donor->prefix, $donation->donor->prefix);
        self::assertSame($donor->email, $donation->donor->email);
    }
}
