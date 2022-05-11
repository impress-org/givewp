<?php

declare(strict_types=1);

use Give\Donations\LegacyListeners\DispatchGivePreInsertPayment;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;

/**
 * @coversDefaultClass DispatchGivePreInsertPayment
 */
class DispatchGivePreInsertPaymentTest extends Give_Unit_Test_Case
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donationsTable = DB::prefix('posts');
        $donationMetaTable = DB::prefix('give_donationmeta');
        $donorTable = DB::prefix('give_donors');
        $donorMetaTable = DB::prefix('give_donormeta');
        $subscriptionsTable = DB::prefix('give_subscriptions');
        $sequentialOrderingTable = DB::prefix('give_sequential_ordering');

        DB::query("TRUNCATE TABLE $donorTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
        DB::query("TRUNCATE TABLE $donationMetaTable");
        DB::query("TRUNCATE TABLE $donationsTable");
        DB::query("TRUNCATE TABLE $subscriptionsTable");
        DB::query("TRUNCATE TABLE $sequentialOrderingTable");
    }

    /**
     * @unreleased
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
