<?php

namespace Give\Tests\Unit\Donations\Migrations;

use Give\Donations\Migrations\RecalculateExchangeRate;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class RecalculateExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var RecalculateExchangeRate
     */
    private $migration;

    public function setUp(): void
    {
        parent::setUp();
        $this->migration = new RecalculateExchangeRate();
    }

    public function testGetBatchSizeReturnsCorrectValue()
    {
        $this->assertEquals(100, $this->migration->getBatchSize());
    }

    public function testRunBatchUpdatesExchangeRates()
    {
        // Create test data
        $donationId = $this->createDonationWithMeta([
            DonationMetaKeys::AMOUNT => '100.00',
            DonationMetaKeys::BASE_AMOUNT => '80.00',
            DonationMetaKeys::CURRENCY => 'USD',
            '_give_cs_base_currency' => 'EUR',
            DonationMetaKeys::EXCHANGE_RATE => '1'
        ]);

        // Run the migration
        $this->migration->runBatch(0, $donationId + 1);

        // Verify the exchange rate was updated
        $updatedExchangeRate = give()->payment_meta->get_meta($donationId, DonationMetaKeys::EXCHANGE_RATE, true);

        $this->assertNotEmpty($updatedExchangeRate);
        $this->assertEquals('1.25', $updatedExchangeRate);
    }

    public function testGetItemsCountReturnsCorrectCount()
    {
        $this->createDonationWithMeta([
            DonationMetaKeys::AMOUNT => '100.00',
            DonationMetaKeys::BASE_AMOUNT => '80.00',
            DonationMetaKeys::CURRENCY => 'USD',
            '_give_cs_base_currency' => 'EUR',
            DonationMetaKeys::EXCHANGE_RATE => '1'
        ]);

        $count = $this->migration->getItemsCount();
        $this->assertEquals(1, $count);
    }

    public function testHasMoreItemsToBatchReturnsCorrectValue()
    {
        $donationId = $this->createDonationWithMeta([
            DonationMetaKeys::AMOUNT => '100.00',
            DonationMetaKeys::BASE_AMOUNT => '80.00',
            DonationMetaKeys::CURRENCY => 'USD',
            '_give_cs_base_currency' => 'EUR',
            DonationMetaKeys::EXCHANGE_RATE => '1'
        ]);

        $this->assertTrue($this->migration->hasMoreItemsToBatch(0));
        $this->assertFalse($this->migration->hasMoreItemsToBatch($donationId + 1));
    }

    private function createDonationWithMeta(array $meta): int
    {
        $donationId = Donation::factory()->create()->id;

        foreach ($meta as $metaKey => $metaValue) {
            give()->payment_meta->update_meta($donationId, $metaKey, $metaValue);
        }

        return $donationId;
    }
}
