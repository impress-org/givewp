<?php

namespace Give\Tests\Unit\Subscriptions\Models;

use DateTime;
use Exception;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Framework\Database\DB;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationType;

/**
 * @since 2.19.6
 *
 * @coversDefaultClass \Give\Subscriptions\Models\Subscription
 */
class TestSubscription extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldInsertSubscription()
    {
        $subscription = Subscription::factory()->createWithDonation();

        /** @var Subscription $subscriptionFromDatabase */
        $subscriptionFromDatabase = Subscription::find($subscription->id);

        $this->assertEquals($subscription->toArray(), $subscriptionFromDatabase->toArray());
    }

    /**
     * @since 3.20.0
     * @throws Exception
     */
    public function testCreateRenewalShouldCreateRenewalWithAttributes(): void
    {
        $subscription = Subscription::factory()->createWithDonation();

        /** @var Subscription $subscriptionFromDatabase */
        $subscriptionFromDatabase = Subscription::find($subscription->id);
        $renewal = $subscriptionFromDatabase->createRenewal(['gatewayTransactionId' => 'transaction-id-1234']);

        $this->assertEquals('transaction-id-1234', $renewal->gatewayTransactionId);
        $this->assertEquals($subscriptionFromDatabase->id, $renewal->subscriptionId);
    }

    /**
     * @since 3.20.0
     */
    public function testShouldCreateRenewalShouldReturnTrueWhenInstallmentsIsZero()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 0,
        ]);

        $this->assertTrue($subscription->shouldCreateRenewal());


    }

    /**
     * @since 3.20.0
     */
    public function testShouldCreateRenewalShouldReturnTrueWhenInstallmentsIsLessThanDonations(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 3,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertTrue($subscription->shouldCreateRenewal());
    }

    /**
     * @since 3.20.0
     */
    public function testShouldCreateRenewalShouldReturnFalseWhenInstallmentsAreReached(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 2,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertFalse($subscription->shouldCreateRenewal());
    }

    /**
     * @since 3.20.0
     */
    public function testShouldCreateRenewalShouldReturnFalseWhenStatusIsNotActive(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PENDING(),
            'installments' => 0,
        ]);

        $this->assertFalse($subscription->shouldCreateRenewal());
    }

    /**
     * @since 3.20.0
     */
    public function testShouldEndSubscriptionShouldReturnTrueWhenInstallmentsAreReached(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 2,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertTrue($subscription->shouldEndSubscription());
    }

    /**
     * @since 3.20.0
     */
    public function testShouldEndSubscriptionShouldReturnFalseWhenInstallmentsAreNotReached(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 4,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertFalse($subscription->shouldEndSubscription());
    }

    /**
     * @since 3.20.0
     */
    public function testShouldEndSubscriptionShouldReturnFalseWhenInstallmentsAreZero(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 0,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertFalse($subscription->shouldEndSubscription());
    }

    /**
     * @throws Exception
     */
    public function testGetTotalDonationsShouldReturnTotalDonations(): void
    {
        $subscription = Subscription::factory()->createWithDonation();
        Subscription::factory()->createRenewal($subscription, 2);

        $this->assertEquals(3, $subscription->totalDonations());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSubscriptionShouldGetDonations()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $subscription = Subscription::factory()->createWithDonation(['donorId' => $donor->id]);

        Subscription::factory()->createRenewal($subscription, 2, ['donorId' => $donor->id]);

        // include the initial donation with renewals
        $this->assertCount(3, $subscription->donations);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSubscriptionShouldGetDonor()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation(['donorId' => $donor->id]);

        $this->assertSame($donor->id, $subscription->donor->id);
    }

    /**
     * @since 2.19.6
     */
    public function testIntendedAmount()
    {
        // No amount recovered yields same amount
        $subscription = Subscription::factory()->createWithDonation([
            'amount' => new Money(10000, 'USD'),
            'feeAmountRecovered' => new Money(0, 'USD'),
        ]);

        self::assertMoneyEquals(new Money(10000, 'USD'), $subscription->intendedAmount());

        // Intended amount is amount minus fee recovered
        $subscription = Subscription::factory()->create([
            'amount' => new Money(10000, 'USD'),
            'feeAmountRecovered' => new Money(500, 'USD'),
        ]);

        self::assertMoneyEquals(new Money(9500, 'USD'), $subscription->intendedAmount());
    }

    /**
     * @since 2.19.6
     */
    public function testShouldBumpRenewalDate()
    {
        $subscription = Subscription::factory()->create([
            'frequency' => 1,
            'period' => SubscriptionPeriod::MONTH(),
            'renewsAt' => new DateTime('2020-01-01 00:00:00'),
        ]);

        $subscription->bumpRenewalDate();

        $this->assertEquals('2020-02-01 00:00:00', $subscription->renewsAt->format('Y-m-d H:i:s'));
    }

    /**
     * @since 2.19.6
     */
    public function testShouldRetrieveInitialDonationForSubscription()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $this->assertEquals($subscription->donations[0]->id, $subscription->initialDonation()->id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSubscriptionShouldCancel()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSubscriptionShouldGetNotes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testQueryShouldReturnAllRequiredValues()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));
        
        // Create a subscription with all required fields
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => 'test-gateway',
            'amount' => new Money(10000, 'USD'),
            'feeAmountRecovered' => new Money(500, 'USD'),
            'status' => SubscriptionStatus::ACTIVE(),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'transactionId' => 'test-transaction-123',
            'gatewaySubscriptionId' => 'test-subscription-456',
        ]);

        // Query the subscription using the query() method
        $queriedSubscriptions = Subscription::query()
            ->where('id', $subscription->id)
            ->getAll();

        // Assert that the queried subscription is not null
        $this->assertNotEmpty($queriedSubscriptions, 'Subscription should be found via query()');
        $queriedSubscription = $queriedSubscriptions[0];

        // Assert that all required properties are present and not null
        $this->assertNotNull($queriedSubscription->id, 'Subscription ID should not be null');
        $this->assertNotNull($queriedSubscription->donorId, 'Donor ID should not be null');
        $this->assertNotNull($queriedSubscription->donationFormId, 'Donation Form ID should not be null');
        $this->assertNotNull($queriedSubscription->createdAt, 'Created At should not be null');
        $this->assertNotNull($queriedSubscription->amount, 'Amount should not be null');
        $this->assertNotNull($queriedSubscription->feeAmountRecovered, 'Fee Amount Recovered should not be null');
        $this->assertNotNull($queriedSubscription->status, 'Status should not be null');
        $this->assertNotNull($queriedSubscription->period, 'Period should not be null');
        $this->assertNotNull($queriedSubscription->frequency, 'Frequency should not be null');
        $this->assertNotNull($queriedSubscription->installments, 'Installments should not be null');
        $this->assertNotNull($queriedSubscription->transactionId, 'Transaction ID should not be null');
        $this->assertNotNull($queriedSubscription->mode, 'Mode should not be null');

        // Specifically test gatewayId and currency (via amount) which are critical meta keys
        $this->assertNotNull($queriedSubscription->gatewayId, 'Gateway ID should not be null');
        $this->assertNotEmpty($queriedSubscription->gatewayId, 'Gateway ID should not be empty');
        $this->assertEquals('test-gateway', $queriedSubscription->gatewayId, 'Gateway ID should match the created value');

        // Test that the amount has a valid currency (USD in this case)
        $this->assertNotNull($queriedSubscription->amount->getCurrency(), 'Amount currency should not be null');
        $this->assertEquals('USD', $queriedSubscription->amount->getCurrency(), 'Amount currency should be USD');

        // Test that feeAmountRecovered also has a valid currency
        $this->assertNotNull($queriedSubscription->feeAmountRecovered->getCurrency(), 'Fee amount currency should not be null');
        $this->assertEquals('USD', $queriedSubscription->feeAmountRecovered->getCurrency(), 'Fee amount currency should be USD');

        // Verify that the queried subscription matches the original
        $this->assertEquals($subscription->id, $queriedSubscription->id, 'Subscription ID should match');
        $this->assertEquals($subscription->gatewayId, $queriedSubscription->gatewayId, 'Gateway ID should match');
        $this->assertEquals($subscription->amount->getAmount(), $queriedSubscription->amount->getAmount(), 'Amount should match');
        $this->assertEquals($subscription->amount->getCurrency(), $queriedSubscription->amount->getCurrency(), 'Currency should match');
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateLegacyParentPaymentIdShouldUpdateParentPaymentIdInDatabase()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));
        
        // Create a subscription with donation
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => 'test-gateway',
            'amount' => new Money(10000, 'USD'),
            'status' => SubscriptionStatus::ACTIVE(),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'transactionId' => 'test-transaction-123',
        ]);

        // Get the initial donation created with the subscription
        $initialDonation = $subscription->initialDonation();
        
        // Verify that the initial donation exists and has an ID
        $this->assertNotNull($initialDonation, 'Initial donation should exist');
        $this->assertNotNull($initialDonation->id, 'Initial donation should have an ID');

        // Check the parent_payment_id in the database before the update using direct DB query
        $subscriptionBeforeUpdate = DB::table('give_subscriptions')
            ->where('id', $subscription->id)
            ->get();

        // The parent_payment_id should be set automatically during creation
        $this->assertNotEquals(0, $subscriptionBeforeUpdate->parent_payment_id ?? 0, 'Parent payment ID should be set automatically during creation');

        // Manually call the updateLegacyParentPaymentId method
        give()->subscriptions->updateLegacyParentPaymentId($subscription->id, $initialDonation->id);

        // Query the subscription again to check if parent_payment_id was updated
        $subscriptionAfterUpdate = DB::table('give_subscriptions')
            ->where('id', $subscription->id)
            ->get();

        // Verify that parent_payment_id was updated to the initial donation ID
        $this->assertEquals($initialDonation->id, $subscriptionAfterUpdate->parent_payment_id, 'Parent payment ID should be updated to initial donation ID');

        // Verify that gatewayId is now available in the queried subscription
        $queriedSubscription = Subscription::query()
            ->where('id', $subscription->id)
            ->getAll()[0];

        $this->assertNotNull($queriedSubscription->gatewayId, 'Gateway ID should not be null after parent payment ID update');
        $this->assertEquals('test-gateway', $queriedSubscription->gatewayId, 'Gateway ID should match the created value');

        // Verify that the subscription can be found via direct database query
        $subscriptionFromDatabase = Subscription::find($subscription->id);
        $this->assertNotNull($subscriptionFromDatabase, 'Subscription should be found via find() method');
        $this->assertEquals('test-gateway', $subscriptionFromDatabase->gatewayId, 'Gateway ID should be available in found subscription');
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateWithDonationShouldAutomaticallyUpdateParentPaymentId()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));
        
        // Create a subscription with donation - this should automatically trigger updateLegacyParentPaymentId
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => 'test-gateway',
            'amount' => new Money(10000, 'USD'),
            'status' => SubscriptionStatus::ACTIVE(),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'transactionId' => 'test-transaction-123',
        ]);

        // Get the initial donation
        $initialDonation = $subscription->initialDonation();
        
        // Verify that the initial donation exists
        $this->assertNotNull($initialDonation, 'Initial donation should exist');
        $this->assertNotNull($initialDonation->id, 'Initial donation should have an ID');

        // Query the subscription to check if parent_payment_id was automatically updated using direct DB query
        $subscriptionFromDB = DB::table('give_subscriptions')
            ->where('id', $subscription->id)
            ->get();

        // Check if parent_payment_id was set correctly
        $this->assertEquals($initialDonation->id, $subscriptionFromDB->parent_payment_id, 'Parent payment ID should be automatically updated to initial donation ID');

        // Verify that gatewayId is available in the queried subscription
        $queriedSubscription = Subscription::query()
            ->where('id', $subscription->id)
            ->getAll()[0];

        $this->assertNotNull($queriedSubscription->gatewayId, 'Gateway ID should not be null');
        $this->assertEquals('test-gateway', $queriedSubscription->gatewayId, 'Gateway ID should match the created value');

        // Verify that the subscription can be found via find() method
        $subscriptionFromFind = Subscription::find($subscription->id);
        $this->assertNotNull($subscriptionFromFind, 'Subscription should be found via find() method');
        $this->assertEquals('test-gateway', $subscriptionFromFind->gatewayId, 'Gateway ID should be available');
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testQueryShouldReturnGatewayIdWhenParentPaymentIdIsSet()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        // Create a subscription with donation
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => 'test-gateway',
            'amount' => new Money(10000, 'USD'),
            'status' => SubscriptionStatus::ACTIVE(),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'transactionId' => 'test-transaction-123',
        ]);

        // Verify that parent_payment_id is set (this should happen automatically)
        $subscriptionFromDB = DB::table('give_subscriptions')
            ->where('id', $subscription->id)
            ->get();

        $this->assertNotEquals(0, $subscriptionFromDB->parent_payment_id ?? 0, 'Parent payment ID should be set automatically');

        // Query the subscription using Subscription::query() to check if gatewayId is returned
        $queriedSubscriptions = Subscription::query()
            ->where('id', $subscription->id)
            ->getAll();

        $this->assertNotEmpty($queriedSubscriptions, 'Subscription should be found via query()');
        $queriedSubscription = $queriedSubscriptions[0];

        // Assert that the gatewayId is returned correctly
        $this->assertNotNull($queriedSubscription->gatewayId, 'Gateway ID should not be null when parent_payment_id is set');
        $this->assertEquals('test-gateway', $queriedSubscription->gatewayId, 'Gateway ID should match the expected value');

        // Also verify that currency is returned correctly
        $this->assertNotNull($queriedSubscription->amount, 'Amount should not be null');
        $this->assertEquals('USD', $queriedSubscription->amount->getCurrency(), 'Currency should match the expected value');
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testProjectedAnnualRevenueShouldCalculateCorrectly()
    {
        // Create a subscription with monthly frequency
        $subscription = Subscription::factory()->createWithDonation([
            'amount' => new Money(10000, 'USD'), // $100.00
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
        ]);

        // Create some completed donations for the current year
        $this->createCompletedDonationsForSubscription($subscription, 3);

        // Get projected annual revenue
        $projectedRevenue = $subscription->projectedAnnualRevenue();

        // Should be a Money object with USD currency
        $this->assertInstanceOf(Money::class, $projectedRevenue);
        $this->assertEquals('USD', $projectedRevenue->getCurrency());

        // The projected revenue should be greater than 0
        $this->assertGreaterThan(0, $projectedRevenue->getAmount());
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testProjectedAnnualRevenueBasicFunctionality()
    {
        // Create a simple subscription without additional donations
        $subscription = Subscription::factory()->createWithDonation([
            'amount' => new Money(10000, 'USD'), // $100.00
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
        ]);

        // Get projected annual revenue
        $projectedRevenue = $subscription->projectedAnnualRevenue();

        // Should be a Money object with USD currency
        $this->assertInstanceOf(Money::class, $projectedRevenue);
        $this->assertEquals('USD', $projectedRevenue->getCurrency());

        // The projected revenue should be greater than 0 (even without additional donations)
        $this->assertGreaterThan(0, $projectedRevenue->getAmount());
    }


    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetRemainingDonationCountUntilEndOfYearShouldCalculateCorrectly()
    {
        // Create a subscription with monthly frequency
        $subscription = Subscription::factory()->createWithDonation([
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
        ]);

        $remainingDonations = $subscription->getRemainingDonationCountUntilEndOfYear();

        // Should return a non-negative integer
        $this->assertIsInt($remainingDonations);
        $this->assertGreaterThanOrEqual(0, $remainingDonations);

        // For a monthly subscription, should be reasonable (0-12)
        $this->assertLessThanOrEqual(12, $remainingDonations);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetRemainingDonationCountUntilEndOfYearShouldRespectInstallments()
    {
        // Create a subscription with limited installments
        $subscription = Subscription::factory()->createWithDonation([
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 3, // Only 3 total installments
        ]);

        // Create 2 renewals (total 3 donations including initial)
        Subscription::factory()->createRenewal($subscription, 2);

        $remainingDonations = $subscription->getRemainingDonationCountUntilEndOfYear();

        // Should be 0 since all installments are used
        $this->assertEquals(0, $remainingDonations);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetRemainingDonationCountUntilEndOfYearShouldHandleDifferentPeriods()
    {
        $testCases = [
            [SubscriptionPeriod::DAY(), 1, 365], // Daily subscription
            [SubscriptionPeriod::WEEK(), 1, 52], // Weekly subscription
            [SubscriptionPeriod::MONTH(), 1, 12], // Monthly subscription
            [SubscriptionPeriod::QUARTER(), 1, 4], // Quarterly subscription
            [SubscriptionPeriod::YEAR(), 1, 1], // Yearly subscription
        ];

        foreach ($testCases as [$period, $frequency, $expectedMaxPerYear]) {
            $subscription = Subscription::factory()->createWithDonation([
                'period' => $period,
                'frequency' => $frequency,
                'installments' => 0,
            ]);

            $remainingDonations = $subscription->getRemainingDonationCountUntilEndOfYear();

            // Should return a non-negative integer
            $this->assertIsInt($remainingDonations);
            $this->assertGreaterThanOrEqual(0, $remainingDonations);

            // Should not exceed the maximum possible for the period
            $this->assertLessThanOrEqual($expectedMaxPerYear, $remainingDonations);
        }
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetRemainingDonationCountUntilEndOfYearShouldHandleFrequency()
    {
        // Test different frequencies for monthly subscriptions
        $testCases = [
            [1, 12], // Monthly (every 1 month)
            [2, 6],  // Every 2 months
            [3, 4],  // Every 3 months
            [6, 2],  // Every 6 months
        ];

        foreach ($testCases as [$frequency, $expectedMaxPerYear]) {
            $subscription = Subscription::factory()->createWithDonation([
                'period' => SubscriptionPeriod::MONTH(),
                'frequency' => $frequency,
                'installments' => 0,
            ]);

            $remainingDonations = $subscription->getRemainingDonationCountUntilEndOfYear();

            // Should return a non-negative integer
            $this->assertIsInt($remainingDonations);
            $this->assertGreaterThanOrEqual(0, $remainingDonations);

            // Should not exceed the maximum possible for the frequency
            $this->assertLessThanOrEqual($expectedMaxPerYear, $remainingDonations);
        }
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetNumericPeriodValueIndirectlyThroughCalculations()
    {
        // Test that different periods result in different calculation results
        $testCases = [
            [SubscriptionPeriod::DAY(), 1, 365], // Daily subscription
            [SubscriptionPeriod::WEEK(), 1, 52], // Weekly subscription
            [SubscriptionPeriod::MONTH(), 1, 12], // Monthly subscription
            [SubscriptionPeriod::QUARTER(), 1, 4], // Quarterly subscription
            [SubscriptionPeriod::YEAR(), 1, 1], // Yearly subscription
        ];

        foreach ($testCases as [$period, $frequency, $expectedMaxPerYear]) {
            $subscription = Subscription::factory()->createWithDonation([
                'period' => $period,
                'frequency' => $frequency,
                'installments' => 0,
            ]);

            $remainingDonations = $subscription->getRemainingDonationCountUntilEndOfYear();

            // Should return a non-negative integer
            $this->assertIsInt($remainingDonations);
            $this->assertGreaterThanOrEqual(0, $remainingDonations);

            // Should not exceed the maximum possible for the period
            $this->assertLessThanOrEqual($expectedMaxPerYear, $remainingDonations);

            // Test that the calculation logic works correctly for each period
            if ($period->getValue() === 'month') {
                // For monthly subscriptions, remaining donations should be reasonable
                $this->assertLessThanOrEqual(12, $remainingDonations);
            } elseif ($period->getValue() === 'year') {
                // For yearly subscriptions, remaining donations should be 0 or 1
                $this->assertLessThanOrEqual(1, $remainingDonations);
            }
        }
    }

    /**
     * Helper method to create completed donations for a subscription
     *
     * @param Subscription $subscription
     * @param int $count
     * @return void
     */
    private function createCompletedDonationsForSubscription(Subscription $subscription, int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $donation = Donation::factory()->create([
                'amount' => $subscription->amount,
                'status' => DonationStatus::COMPLETE(),
                'type' => DonationType::RENEWAL(),
                'subscriptionId' => $subscription->id,
                'gatewayId' => $subscription->gatewayId,
            ]);

            // Add subscription meta to link donation to subscription
            DB::table('give_donationmeta')->insert([
                'donation_id' => $donation->id,
                'meta_key' => DonationMetaKeys::SUBSCRIPTION_ID,
                'meta_value' => $subscription->id,
            ]);
        }
    }
}
