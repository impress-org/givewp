<?php

namespace Give\Tests\Unit\Donations\Storage;

use Give\Donations\Migrations\CreateDonationsTable;
use Give\Donations\Models\Donation;
use Give\Donations\Storage\DonationStorageDriftCheck;
use Give\Donations\Storage\DonationStorageFlag;
use Give\Donations\Storage\DonationTableSync;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class TestDonationStorageDriftCheck extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();
        delete_option(DonationStorageFlag::OPTION);
        delete_option(DonationStorageDriftCheck::OPTION);
        (new CreateDonationsTable())->run();
    }

    /**
     * @since TBD
     */
    private function row(int $donationId)
    {
        return DB::get_row(DB::prepare('SELECT * FROM ' . DB::prefix('give_donations') . ' WHERE id = %d', $donationId));
    }

    /**
     * @since TBD
     */
    public function testStoragesInStepReportNoDrift()
    {
        Donation::factory()->create(['amount' => new Money(1500, 'USD'), 'status' => DonationStatus::COMPLETE()]);
        Donation::factory()->create(['amount' => new Money(2500, 'EUR'), 'status' => DonationStatus::PENDING()]);
        $subscription = Subscription::factory()->createWithDonation();
        Donation::factory()->create([
            'donorId' => $subscription->donorId,
            'subscriptionId' => $subscription->id,
            'type' => DonationType::RENEWAL(),
            'status' => DonationStatus::COMPLETE(),
        ]);

        $report = (new DonationStorageDriftCheck())->run();

        $this->assertFalse($report['drift'], json_encode($report));
        $this->assertSame(4, $report['counts']['test']['posts']);
        $this->assertSame(4, $report['counts']['test']['table']);
        $this->assertSame($report, get_option(DonationStorageDriftCheck::OPTION));
    }

    /**
     * @since TBD
     */
    public function testStatusChangedBehindTheSyncIsReported()
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        DB::table('posts')->where('ID', $donation->id)->update(['post_status' => 'publish']);

        $report = (new DonationStorageDriftCheck())->run();

        $this->assertTrue($report['drift']);
        $this->assertSame(1, $report['status']['count']);
        $this->assertSame([$donation->id], $report['status']['sampleIds']);
        $this->assertSame(0, $report['amount']['count']);
    }

    /**
     * @since TBD
     */
    public function testAmountChangedBehindTheSyncIsReported()
    {
        $donation = Donation::factory()->create(['amount' => new Money(1000, 'USD')]);

        DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', DonationMetaKeys::AMOUNT)
            ->update(['meta_value' => '12.00']);

        $report = (new DonationStorageDriftCheck())->run();

        $this->assertTrue($report['drift']);
        $this->assertSame([$donation->id], $report['amount']['sampleIds']);
        $this->assertSame(1200, $report['sums'][0]['posts']);
        $this->assertSame(1000, $report['sums'][0]['table']);
    }

    /**
     * Two post shapes that the decoder maps correctly and a naive inverse would flag: a renewal
     * whose post_status is publish rather than give_subscription, and a flagged initial donation
     * with no subscription_id meta.
     *
     * @since TBD
     */
    public function testCorrectlyDecodedEdgeCasesAreNotDrift()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $renewal = Donation::factory()->create([
            'donorId' => $subscription->donorId,
            'subscriptionId' => $subscription->id,
            'type' => DonationType::RENEWAL(),
            'status' => DonationStatus::COMPLETE(),
        ]);
        give_update_payment_status($renewal->id, 'publish');

        $flagged = Donation::factory()->create();
        give()->payment_meta->add_meta($flagged->id, DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION, 1);

        give(DonationTableSync::class)->flush();

        $this->assertSame('publish', get_post_status($renewal->id));
        $this->assertSame('renewal', $this->row($renewal->id)->type);
        $this->assertSame('subscription', $this->row($flagged->id)->type);

        $report = (new DonationStorageDriftCheck())->run();

        $this->assertFalse($report['drift'], json_encode($report));
    }

    /**
     * @since TBD
     */
    public function testTypeAndModeChangedBehindTheSyncAreReported()
    {
        $flagged = Donation::factory()->create();
        $modeChanged = Donation::factory()->create();

        give()->payment_meta->add_meta($flagged->id, DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION, 1);
        DB::table('give_donationmeta')
            ->where('donation_id', $modeChanged->id)
            ->where('meta_key', DonationMetaKeys::MODE)
            ->update(['meta_value' => 'live']);
        DB::table('give_donations')->where('id', $flagged->id)->update(['type' => 'single']);

        $report = (new DonationStorageDriftCheck())->run();

        $this->assertSame([$flagged->id], $report['type']['sampleIds']);
        $this->assertSame([$modeChanged->id], $report["mode"]["sampleIds"], json_encode($report));
        $this->assertSame(0, $report['status']['count']);
    }

    /**
     * Legacy amounts with more precision than the currency are rounded per donation on both
     * sides, so the sums agree whenever every row agrees.
     *
     * @since TBD
     */
    public function testSumsRoundEachDonationLikeTheSync()
    {
        $donations = Donation::factory()->count(3)->create();

        foreach ($donations as $donation) {
            DB::table('give_donationmeta')
                ->where('donation_id', $donation->id)
                ->where('meta_key', DonationMetaKeys::AMOUNT)
                ->update(['meta_value' => '10.005']);
        }
        give(DonationTableSync::class)->sync(...array_column($donations, 'id'));

        $report = (new DonationStorageDriftCheck())->run();

        $this->assertFalse($report['drift'], json_encode($report));
        $this->assertSame(3003, $report['sums'][0]['posts']);
        $this->assertSame(3003, $report['sums'][0]['table']);
    }

    /**
     * Legacy add_meta() is not unique. A duplicated key must read like get_metadata() does, first
     * row wins, and never count as drift or double a sum.
     *
     * @since TBD
     */
    public function testDuplicateMetaRowsAreNotDrift()
    {
        $donation = Donation::factory()->create(['amount' => new Money(1000, 'USD')]);

        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation->id,
            'meta_key' => DonationMetaKeys::AMOUNT,
            'meta_value' => '99.00',
        ]);
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation->id,
            'meta_key' => DonationMetaKeys::MODE,
            'meta_value' => 'live',
        ]);

        $report = (new DonationStorageDriftCheck())->run();

        $this->assertFalse($report['drift'], json_encode($report));
        $this->assertSame(1, $report['counts']['test']['posts']);
        $this->assertSame(1000, $report['sums'][0]['posts']);
    }

    /**
     * @since TBD
     */
    public function testRowsMissingFromEitherSideAreReported()
    {
        $kept = Donation::factory()->create();
        $removedFromTable = Donation::factory()->create();
        $removedFromPosts = Donation::factory()->create();

        DB::table('give_donations')->where('id', $removedFromTable->id)->delete();
        DB::table('posts')->where('ID', $removedFromPosts->id)->delete();

        $report = (new DonationStorageDriftCheck())->run();

        $this->assertTrue($report['drift']);
        $this->assertSame([$removedFromTable->id], $report['missingFromTable']['sampleIds']);
        $this->assertSame([$removedFromPosts->id], $report['missingFromPosts']['sampleIds']);
        $this->assertSame(2, $report['counts']['test']['posts']);
        $this->assertSame(2, $report['counts']['test']['table']);
        $this->assertNotContains($kept->id, $report['missingFromTable']['sampleIds']);
    }
}
