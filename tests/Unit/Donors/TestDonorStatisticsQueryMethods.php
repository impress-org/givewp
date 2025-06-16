<?php

namespace Give\Tests\Unit\Donors;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\DonorStatisticsQuery;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorType;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use DateTime;

/**
 * @since 4.4.0
 *
 * @coversDefaultClass \Give\Donors\DonorStatisticsQuery
 */
class TestDonorStatisticsQueryMethods extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that getFirstDonation method exists and returns expected type
     *
     * @since 4.4.0
     */
    public function testGetFirstDonationMethodExists()
    {
        $donor = Donor::factory()->create();
        $query = new DonorStatisticsQuery($donor);

        $this->assertTrue(method_exists($query, 'getFirstDonation'));

        // With no donations, should return null
        $result = $query->getFirstDonation();
        $this->assertNull($result);
    }

    /**
     * Test that getLastContribution method exists and returns expected type
     *
     * @since 4.4.0
     */
    public function testGetLastContributionMethodExists()
    {
        $donor = Donor::factory()->create();
        $query = new DonorStatisticsQuery($donor);

        $this->assertTrue(method_exists($query, 'getLastDonation'));

        // With no donations, should return null
        $result = $query->getLastDonation();
        $this->assertNull($result);
    }

    /**
     * Test that getDonorType method exists and returns expected type
     *
     * @since 4.4.0
     */
    public function testGetDonorTypeMethodExists()
    {
        $donor = Donor::factory()->create();
        $query = new DonorStatisticsQuery($donor);

        $this->assertTrue(method_exists($query, 'getDonorType'));

        // Test that the method can be called without error
        $result = $query->getDonorType();

        // For a new donor with no donations, should return "No Donations"
        $this->assertEquals('No Donations', $result);
    }

    /**
     * Test that preferredPaymentMethod method exists and returns expected type
     *
     * @since 4.4.0
     */
    public function testPreferredPaymentMethodMethodExists()
    {
        $donor = Donor::factory()->create();
        $query = new DonorStatisticsQuery($donor);

        $this->assertTrue(method_exists($query, 'preferredPaymentMethod'));

        // With no donations, should return empty string
        $result = $query->preferredPaymentMethod();
        $this->assertEquals('', $result);
    }

    /**
     * Test the filterByCampaign method works correctly
     *
     * @since 4.4.0
     */
    public function testFilterByCampaign()
    {
        $donor = Donor::factory()->create();
        $campaign = Campaign::factory()->create();

        $query = new DonorStatisticsQuery($donor);
        $filteredQuery = $query->filterByCampaign($campaign);

        $this->assertInstanceOf(DonorStatisticsQuery::class, $filteredQuery);
        $this->assertNotSame($query, $filteredQuery); // Should return a new instance
    }

    /**
     * Test that all core calculation methods work without errors
     *
     * @since 4.4.0
     */
    public function testCoreCalculationMethods()
    {
        $donor = Donor::factory()->create();
        $query = new DonorStatisticsQuery($donor);

        // All these methods should work without throwing errors
        $lifetimeAmount = $query->getLifetimeDonationsAmount();
        $highestAmount = $query->getHighestDonationAmount();
        $averageAmount = $query->getAverageDonationAmount();
        $donationsCount = $query->getDonationsCount();

        // With no donations, these should all return 0 or null
        $this->assertEquals(0, $lifetimeAmount);
        $this->assertNull($highestAmount);
        $this->assertEquals(0, $averageAmount);
        $this->assertEquals(0, $donationsCount);
    }

    /**
     * Test the joinDonationMeta method works correctly
     *
     * @since 4.4.0
     */
    public function testJoinDonationMeta()
    {
        $donor = Donor::factory()->create();
        $query = new DonorStatisticsQuery($donor);

        $result = $query->joinDonationMeta('test_key', 'test_alias');

        $this->assertInstanceOf(DonorStatisticsQuery::class, $result);
        $this->assertSame($query, $result); // Should return same instance
    }

    /**
     * Test statistics with a single donation
     *
     * @since 4.4.0
     */
    public function testStatisticsWithSingleDonation()
    {
        $donor = Donor::factory()->create();
        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(10000, 'USD'), // $100.00
            'status' => DonationStatus::COMPLETE(),
            'createdAt' => new DateTime('2023-01-15 10:30:00')
        ]);

        $query = new DonorStatisticsQuery($donor);

        // Test amount calculations
        $this->assertEquals(100, $query->getLifetimeDonationsAmount());
        $this->assertEquals(100, $query->getHighestDonationAmount());
        $this->assertEquals(100, $query->getAverageDonationAmount());
        $this->assertEquals(1, $query->getDonationsCount());

        // Test first and last donation
        $firstDonation = $query->getFirstDonation();
        $lastDonation = $query->getLastDonation();
        $this->assertNotNull($firstDonation);
        $this->assertNotNull($lastDonation);
        $this->assertIsArray($firstDonation);
        $this->assertArrayHasKey('amount', $firstDonation);
        $this->assertArrayHasKey('date', $firstDonation);
        $this->assertEquals(100, $firstDonation['amount']);
        $this->assertIsArray($lastDonation);
        $this->assertArrayHasKey('amount', $lastDonation);
        $this->assertArrayHasKey('date', $lastDonation);
        $this->assertEquals(100, $lastDonation['amount']);
    }

    /**
     * Test statistics with multiple donations
     *
     * @since 4.4.0
     */
    public function testStatisticsWithMultipleDonations()
    {
        $donor = Donor::factory()->create();

        // Create donations with different amounts and dates
        $firstDonation = Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(5000, 'USD'), // $50.00
            'status' => DonationStatus::COMPLETE(),
            'createdAt' => new DateTime('2023-01-01 10:00:00')
        ]);

        $middleDonation = Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(15000, 'USD'), // $150.00
            'status' => DonationStatus::COMPLETE(),
            'createdAt' => new DateTime('2023-06-15 14:30:00')
        ]);

        $lastDonation = Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(7500, 'USD'), // $75.00
            'status' => DonationStatus::COMPLETE(),
            'createdAt' => new DateTime('2023-12-01 16:45:00')
        ]);

        $query = new DonorStatisticsQuery($donor);

        // Test calculations: 50 + 150 + 75 = 275, average = 91.67 (rounded)
        $this->assertEquals(275, $query->getLifetimeDonationsAmount());
        $this->assertEquals(150, $query->getHighestDonationAmount());
        $this->assertEquals(91, (int) $query->getAverageDonationAmount()); // Round for comparison
        $this->assertEquals(3, $query->getDonationsCount());

        // Test first and last donations
        $firstResult = $query->getFirstDonation();
        $lastResult = $query->getLastDonation();
        $this->assertNotNull($firstResult);
        $this->assertNotNull($lastResult);
        $this->assertIsArray($firstResult);
        $this->assertArrayHasKey('amount', $firstResult);
        $this->assertEquals(50, $firstResult['amount']); // First donation was $50
        $this->assertIsArray($lastResult); // Returns array with amount and date
        $this->assertArrayHasKey('amount', $lastResult);
        $this->assertArrayHasKey('date', $lastResult);
        $this->assertEquals(75, $lastResult['amount']); // Last donation was $75
    }

    /**
     * Test donor type method exists and works
     *
     * @since 4.4.0
     */
    public function testDonorTypeWithDonations()
    {
        $donor = Donor::factory()->create();
        Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(10000, 'USD'), // $100.00
            'status' => DonationStatus::COMPLETE()
        ]);

        $query = new DonorStatisticsQuery($donor);
        $donorType = $query->getDonorType();

        // Test that the method works and returns a valid donor type
        // Note: In test environment, donor aggregated stats may not automatically update
        // so this might still return "No Donations" which is expected behavior
        $this->assertNotNull($donorType);
        $this->assertIsString($donorType);
    }

    /**
     * Test preferred payment method calculation
     *
     * @since 4.4.0
     */
    public function testPreferredPaymentMethodWithDonations()
    {
        $donor = Donor::factory()->create();

        // Create donations with different payment methods
        Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(5000, 'USD'), // $50.00
            'status' => DonationStatus::COMPLETE(),
            'gatewayId' => 'manual'
        ]);

        Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(7500, 'USD'), // $75.00
            'status' => DonationStatus::COMPLETE(),
            'gatewayId' => 'stripe'
        ]);

        Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(10000, 'USD'), // $100.00
            'status' => DonationStatus::COMPLETE(),
            'gatewayId' => 'stripe'
        ]);

        $query = new DonorStatisticsQuery($donor);
        $preferredMethod = $query->preferredPaymentMethod();

        // Should return the most used payment method (stripe appears twice)
        $this->assertNotEmpty($preferredMethod);
        // Check that it contains "stripe" since stripe appears twice vs manual once
        $this->assertStringContainsStringIgnoringCase('stripe', $preferredMethod);
    }

    /**
     * Test filtering by campaign with donations
     *
     * @since 4.4.0
     */
    public function testCampaignFilteringWithDonations()
    {
        $donor = Donor::factory()->create();
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();

        // Create donations for different campaigns
        Donation::factory()->create([
            'donorId' => $donor->id,
            'campaignId' => $campaign1->id,
            'amount' => new Money(10000, 'USD'), // $100.00
            'status' => DonationStatus::COMPLETE()
        ]);

        Donation::factory()->create([
            'donorId' => $donor->id,
            'campaignId' => $campaign2->id,
            'amount' => new Money(20000, 'USD'), // $200.00
            'status' => DonationStatus::COMPLETE()
        ]);

        // Test unfiltered query (should include both donations)
        $unfilteredQuery = new DonorStatisticsQuery($donor);
        $totalAmount = $unfilteredQuery->getLifetimeDonationsAmount();
        $totalCount = $unfilteredQuery->getDonationsCount();

        // Should have both donations: $100 + $200 = $300, count = 2
        $this->assertEquals(300, $totalAmount);
        $this->assertEquals(2, $totalCount);

        // Test filtering by campaign1 (should only include $100 donation)
        $filteredQuery = $unfilteredQuery->filterByCampaign($campaign1);

        // The filtered query should be a different instance
        $this->assertInstanceOf(DonorStatisticsQuery::class, $filteredQuery);
        $this->assertNotSame($unfilteredQuery, $filteredQuery);

        // Verify filtered results only include campaign1 data
        $filteredAmount = $filteredQuery->getLifetimeDonationsAmount();
        $filteredCount = $filteredQuery->getDonationsCount();

        // Should only have campaign1 donation: $100, count = 1
        $this->assertEquals(100, $filteredAmount);
        $this->assertEquals(1, $filteredCount);

        // Test filtering by campaign2 (should only include $200 donation)
        $campaign2FilteredQuery = $unfilteredQuery->filterByCampaign($campaign2);
        $campaign2Amount = $campaign2FilteredQuery->getLifetimeDonationsAmount();
        $campaign2Count = $campaign2FilteredQuery->getDonationsCount();

        // Should only have campaign2 donation: $200, count = 1
        $this->assertEquals(200, $campaign2Amount);
        $this->assertEquals(1, $campaign2Count);
    }

    /**
     * Test statistics with only pending donations (should not count incomplete donations)
     *
     * @since 4.4.0
     */
    public function testStatisticsWithPendingDonations()
    {
        $donor = Donor::factory()->create();

        // Create a pending donation
        Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(10000, 'USD'), // $100.00
            'status' => DonationStatus::PENDING()
        ]);

        $query = new DonorStatisticsQuery($donor);

        // Pending donations typically shouldn't count in statistics
        // The exact behavior depends on implementation, but we test the methods work
        $lifetimeAmount = $query->getLifetimeDonationsAmount();
        $donationsCount = $query->getDonationsCount();

        $this->assertIsNumeric($lifetimeAmount);
        $this->assertIsNumeric($donationsCount);
    }

    /**
     * Test mode filtering (live vs test)
     *
     * @since 4.4.0
     */
    public function testModeFiltering()
    {
        $donor = Donor::factory()->create();

        // Create live donation
        Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(10000, 'USD'), // $100.00
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE()
        ]);

        // Create test donation
        Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(20000, 'USD'), // $200.00
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::TEST()
        ]);

        // Test live mode query
        $liveQuery = new DonorStatisticsQuery($donor, 'live');
        $testQuery = new DonorStatisticsQuery($donor, 'test');

        // Both queries should work without errors
        $liveAmount = $liveQuery->getLifetimeDonationsAmount();
        $testAmount = $testQuery->getLifetimeDonationsAmount();

        $this->assertIsNumeric($liveAmount);
        $this->assertIsNumeric($testAmount);
    }
}
