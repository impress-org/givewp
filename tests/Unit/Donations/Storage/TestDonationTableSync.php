<?php

namespace Give\Tests\Unit\Donations\Storage;

use Give\Donations\Migrations\CreateDonationsTable;
use Give\Donations\Models\Donation;
use Give\Donations\Storage\DonationStorageFlag;
use Give\Donations\Storage\DonationTableSync;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class TestDonationTableSync extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();
        delete_option(DonationStorageFlag::OPTION);
        (new CreateDonationsTable())->run();
    }

    /**
     * @since TBD
     */
    public function testRepositoryInsertWritesRow()
    {
        $donation = Donation::factory()->create([
            'amount' => new Money(12345, 'USD'),
            'feeAmountRecovered' => new Money(345, 'USD'),
            'status' => DonationStatus::COMPLETE(),
        ]);

        $row = $this->row($donation->id);

        $this->assertSame($donation->donorId, (int)$row->donor_id);
        $this->assertSame('publish', $row->status);
        $this->assertSame('single', $row->type);
        $this->assertSame('test', $row->mode);
        $this->assertSame(12345, (int)$row->amount_total);
        $this->assertSame(345, (int)$row->fee_amount_recovered);
        $this->assertSame(12000, (int)$row->amount_intended);
        $this->assertSame('USD', $row->currency);
        $this->assertSame($donation->email, $row->email);
        $this->assertSame($donation->createdAt->format('Y-m-d H:i:s'), get_date_from_gmt($row->created_at));
    }

    /**
     * @since TBD
     */
    public function testRepositoryUpdateTrashUntrashAndDeleteFollowThrough()
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        $donation->status = DonationStatus::COMPLETE();
        $donation->amount = new Money(5000, 'USD');
        $donation->save();
        $row = $this->row($donation->id);
        $this->assertSame('publish', $row->status);
        $this->assertSame(5000, (int)$row->amount_total);

        $donation->trash();
        $this->assertSame('trash', $this->row($donation->id)->status);

        $donation->unTrash();
        $this->assertSame('publish', $this->row($donation->id)->status);

        $donation->delete();
        $this->assertNull($this->row($donation->id));
    }

    /**
     * @since TBD
     */
    public function testRenewalDecodesToCompleteStatusAndRenewalType()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $renewal = Donation::factory()->create([
            'donorId' => $subscription->donorId,
            'subscriptionId' => $subscription->id,
            'type' => DonationType::RENEWAL(),
            'status' => DonationStatus::COMPLETE(),
        ]);

        $this->assertSame('give_subscription', get_post_status($renewal->id));

        $row = $this->row($renewal->id);
        $this->assertSame('publish', $row->status);
        $this->assertSame('renewal', $row->type);
        $this->assertSame($subscription->id, (int)$row->subscription_id);

        $initial = $this->row($subscription->initialDonation()->id);
        $this->assertSame('subscription', $initial->type);
    }

    /**
     * @since TBD
     */
    public function testLegacyInsertAndStatusChangeReachTheTableAtFlush()
    {
        $donor = Donor::factory()->create();
        $donationId = give_insert_payment([
            'price' => '10.50',
            'give_form_title' => 'Legacy Form',
            'give_form_id' => 1,
            'date' => current_time('mysql'),
            'user_email' => $donor->email,
            'purchase_key' => 'legacy-key',
            'currency' => 'USD',
            'user_info' => [
                'id' => 0,
                'email' => $donor->email,
                'first_name' => $donor->firstName,
                'last_name' => $donor->lastName,
            ],
            'status' => 'pending',
            'gateway' => TestGateway::id(),
        ]);

        $this->assertNull($this->row($donationId));

        give(DonationTableSync::class)->flush();

        $row = $this->row($donationId);
        $this->assertSame('pending', $row->status);
        $this->assertSame(1050, (int)$row->amount_total);
        $this->assertSame('legacy-key', $row->purchase_key);

        give_update_payment_status($donationId, 'publish');
        give_update_meta($donationId, DonationMetaKeys::FEE_AMOUNT_RECOVERED, '0.50');
        give(DonationTableSync::class)->flush();

        $row = $this->row($donationId);
        $this->assertSame('publish', $row->status);
        $this->assertSame(50, (int)$row->fee_amount_recovered);
        $this->assertSame(1000, (int)$row->amount_intended);

        give_delete_donation($donationId);
        $this->assertNull($this->row($donationId));
    }

    /**
     * The sequential number is written by a created listener after the repository commits, so
     * it only reaches the table on the shutdown flush.
     *
     * @since TBD
     */
    public function testSequentialNumberArrivesOnFlush()
    {
        give_update_option('sequential-ordering_status', 'enabled');

        $donation = Donation::factory()->create();

        $this->assertNull($this->row($donation->id)->sequential_number);

        give(DonationTableSync::class)->flush();

        $this->assertSame(
            give()->donations->getSequentialId($donation->id),
            (int)$this->row($donation->id)->sequential_number
        );

        give_update_option('sequential-ordering_status', 'disabled');
    }

    /**
     * @since TBD
     */
    public function testCoreTrashAndUntrashFollowThrough()
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);

        wp_trash_post($donation->id);
        give(DonationTableSync::class)->flush();
        $this->assertSame('trash', $this->row($donation->id)->status);

        wp_untrash_post($donation->id);
        give(DonationTableSync::class)->flush();
        $this->assertSame(get_post_status($donation->id), $this->row($donation->id)->status);
    }

    /**
     * @since TBD
     */
    public function testDeletedMetaResyncsTheRow()
    {
        $donation = Donation::factory()->create(['feeAmountRecovered' => new Money(100, 'USD')]);
        $this->assertSame(100, (int)$this->row($donation->id)->fee_amount_recovered);

        give_delete_meta($donation->id, DonationMetaKeys::FEE_AMOUNT_RECOVERED);
        give(DonationTableSync::class)->flush();

        $this->assertNull($this->row($donation->id)->fee_amount_recovered);
    }

    /**
     * A donation queued earlier in the request and gone by flush time is removed from the table.
     *
     * @since TBD
     */
    public function testFlushRemovesRowsWhosePostIsGone()
    {
        $donation = Donation::factory()->create();
        $sync = give(DonationTableSync::class);

        $sync->queue($donation->id);
        DB::table('posts')->where('ID', $donation->id)->delete();
        $this->assertNotNull($this->row($donation->id));

        $sync->flush();

        $this->assertNull($this->row($donation->id));
    }

    /**
     * @since TBD
     */
    public function testQueueFlushesItselfAtTheThreshold()
    {
        update_option(DonationStorageFlag::OPTION, 'posts');
        $donations = Donation::factory()->count(DonationTableSync::FLUSH_THRESHOLD)->create();
        update_option(DonationStorageFlag::OPTION, 'syncing');

        $sync = give(DonationTableSync::class);
        foreach ($donations as $donation) {
            $this->assertNull($this->row($donation->id));
            $sync->queue($donation->id);
        }

        $this->assertSame(DonationTableSync::FLUSH_THRESHOLD, (int)DB::get_var('SELECT COUNT(*) FROM ' . DB::prefix('give_donations')));
    }

    /**
     * @since TBD
     */
    public function testNothingIsWrittenWhileStorageIsPosts()
    {
        update_option(DonationStorageFlag::OPTION, 'posts');

        $donation = Donation::factory()->create();

        $this->assertNull($this->row($donation->id));
    }

    /**
     * @since TBD
     */
    private function row(int $donationId)
    {
        return DB::get_row(DB::prepare('SELECT * FROM ' . DB::prefix('give_donations') . ' WHERE id = %d', $donationId));
    }
}
