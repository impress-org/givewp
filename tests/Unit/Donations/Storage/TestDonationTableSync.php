<?php

namespace Give\Tests\Unit\Donations\Storage;

use Give\Donations\Migrations\CreateDonationsTable;
use DateTime;
use Give\Donations\Models\Donation;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\Repositories\DonationTableRepository;
use Give\Donations\Storage\DonationStorageFlag;
use Give\Donations\Storage\DonationTableSync;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\LogRepository;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give_Payment;
use Give_Subscription;
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

        // Core restores to draft, which is not a donation status; the model reads it as pending
        // and the row holds what the model reads.
        wp_untrash_post($donation->id);
        give(DonationTableSync::class)->flush();
        $this->assertSame('draft', get_post_status($donation->id));
        $this->assertSame(Donation::find($donation->id)->status->getValue(), $this->row($donation->id)->status);
        $this->assertSame('pending', $this->row($donation->id)->status);
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

        $this->assertSame(DonationTableSync::FLUSH_THRESHOLD, (int)DB::get_var('SELECT COUNT(*) FROM ' . DB::prefix(DonationTableRepository::TABLE)));
    }

    /**
     * @since TBD
     */
    public function testLegacyPaymentSaveAndLegacyRenewalReachTheTable()
    {
        $donor = Donor::factory()->create();

        $payment = new Give_Payment();
        $payment->total = 100;
        $payment->form_id = 1;
        $payment->customer_id = $donor->id;
        $payment->first_name = $donor->firstName;
        $payment->last_name = $donor->lastName;
        $payment->email = $donor->email;
        $payment->currency = 'USD';
        $payment->status = 'publish';
        $payment->gateway = TestGateway::id();
        $payment->mode = 'test';
        $payment->save();

        $subscriptionId = (int)(new Give_Subscription())->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $payment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ])->id;
        (new Give_Subscription($subscriptionId))->add_payment([
            'amount' => 80,
            'transaction_id' => 'renewal-txn',
            'gateway' => TestGateway::id(),
        ]);

        give(DonationTableSync::class)->flush();

        // Give_Subscription::create() writes no subscription meta on the parent payment, so the
        // parent decodes as a single donation, exactly as the model reads it from posts today.
        $initial = $this->row($payment->ID);
        $this->assertSame(10000, (int)$initial->amount_total);
        $this->assertSame('publish', $initial->status);
        $this->assertSame('single', $initial->type);

        $renewalId = (int)DB::table('give_donationmeta')
            ->where('meta_key', DonationMetaKeys::GATEWAY_TRANSACTION_ID)
            ->where('meta_value', 'renewal-txn')
            ->value('donation_id');
        $renewal = $this->row($renewalId);
        $this->assertSame('give_subscription', get_post_status($renewalId));
        $this->assertSame('publish', $renewal->status);
        $this->assertSame('renewal', $renewal->type);
        $this->assertSame($subscriptionId, (int)$renewal->subscription_id);
        $this->assertSame(8000, (int)$renewal->amount_total);
    }

    /**
     * Every column, from a donation with every field set, through the repository path.
     *
     * @since TBD
     */
    public function testEveryColumnIsMapped()
    {
        give_update_option('sequential-ordering_status', 'enabled');
        $donor = Donor::factory()->create();
        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'campaignId' => 7,
            'formId' => 9,
            'status' => DonationStatus::COMPLETE(),
            'type' => DonationType::SINGLE(),
            'mode' => DonationMode::LIVE(),
            'gatewayId' => 'stripe',
            'gatewayTransactionId' => 'pi_123',
            'amount' => new Money(123456, 'EUR'),
            'feeAmountRecovered' => new Money(456, 'EUR'),
            'exchangeRate' => '1.25',
            'purchaseKey' => 'key-abc',
            'email' => 'donor@example.com',
            'honorific' => 'Dr',
            'firstName' => 'Ada',
            'lastName' => 'Lovelace',
            'phone' => '+1555',
            'company' => 'Analytical Engines',
            'billingAddress' => BillingAddress::fromArray([
                'address1' => '1 Main St',
                'address2' => 'Suite 2',
                'city' => 'London',
                'state' => 'LDN',
                'zip' => 'N1',
                'country' => 'GB',
            ]),
            'anonymous' => true,
            'createdAt' => new DateTime('2026-01-02 03:04:05'),
        ]);
        give(DonationTableSync::class)->flush();
        give_update_option('sequential-ordering_status', 'disabled');

        $row = (array)$this->row($donation->id);
        unset($row['updated_at']);

        $this->assertSame([
            'id' => (string)$donation->id,
            'donor_id' => (string)$donor->id,
            'campaign_id' => '7',
            'form_id' => '9',
            'subscription_id' => null,
            'status' => 'publish',
            'type' => 'single',
            'mode' => 'live',
            'gateway_id' => 'stripe',
            'gateway_transaction_id' => 'pi_123',
            'amount_total' => '123456',
            'fee_amount_recovered' => '456',
            'currency' => 'EUR',
            'base_currency' => give_get_option('currency', 'USD'),
            'exchange_rate' => '1.2500000000',
            'amount_intended' => '123000',
            'amount_intended_base' => '98400',
            'sequential_number' => (string)give()->donations->getSequentialId($donation->id),
            'purchase_key' => 'key-abc',
            'email' => 'donor@example.com',
            'honorific' => 'Dr',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'phone' => '+1555',
            'company' => 'Analytical Engines',
            'billing_address1' => '1 Main St',
            'billing_address2' => 'Suite 2',
            'billing_city' => 'London',
            'billing_state' => 'LDN',
            'billing_zip' => 'N1',
            'billing_country' => 'GB',
            'anonymous' => '1',
            'created_at' => get_gmt_from_date('2026-01-02 03:04:05'),
        ], $row);
    }

    /**
     * Bad or missing meta falls back the way the model does: mode to live, currency to the site
     * currency, exchange rate to 1, and a zero GMT date converts from the local one.
     *
     * @since TBD
     */
    public function testMetaFallbacks()
    {
        $donation = Donation::factory()->create(['amount' => new Money(500, 'USD')]);

        DB::table('give_donationmeta')->where('donation_id', $donation->id)->where('meta_key', DonationMetaKeys::MODE)->update(['meta_value' => 'bogus']);
        DB::table('give_donationmeta')->where('donation_id', $donation->id)->where('meta_key', DonationMetaKeys::CURRENCY)->update(['meta_value' => '']);
        DB::table('give_donationmeta')->where('donation_id', $donation->id)->where('meta_key', DonationMetaKeys::EXCHANGE_RATE)->update(['meta_value' => '0']);
        DB::table('posts')->where('ID', $donation->id)->update(['post_date_gmt' => '0000-00-00 00:00:00', 'post_date' => '2026-03-04 05:06:07']);

        give(DonationTableSync::class)->sync($donation->id);

        $row = $this->row($donation->id);
        $this->assertSame('live', $row->mode);
        $this->assertSame(give_get_option('currency', 'USD'), $row->currency);
        $this->assertSame('1.0000000000', $row->exchange_rate);
        $this->assertSame(get_gmt_from_date('2026-03-04 05:06:07'), $row->created_at);
    }

    /**
     * SOFT-5084: if either write fails, neither happens.
     *
     * @since TBD
     */
    public function testRepositoryWriteRollsBackWhenTheTableWriteFails()
    {
        $this->breakTheTable();

        try {
            Donation::factory()->create();
            $this->fail('Expected the donation insert to fail');
        } catch (Exception $exception) {
            $this->assertSame('Failed creating a donation', $exception->getMessage());
        }

        $this->assertSame(0, DB::table('posts')->where('post_type', 'give_payment')->count());
    }

    /**
     * A legacy write already happened by the time the queue flushes, so a table failure is
     * logged and swallowed rather than breaking the request.
     *
     * @since TBD
     */
    public function testLegacyFlushLogsInsteadOfThrowingWhenTheTableWriteFails()
    {
        $donor = Donor::factory()->create();
        $donationId = give_insert_payment([
            'price' => '5.00',
            'give_form_title' => 'Legacy Form',
            'give_form_id' => 1,
            'date' => current_time('mysql'),
            'user_email' => $donor->email,
            'purchase_key' => 'legacy-key-2',
            'currency' => 'USD',
            'user_info' => ['id' => 0, 'email' => $donor->email, 'first_name' => 'A', 'last_name' => 'B'],
            'status' => 'pending',
            'gateway' => TestGateway::id(),
        ]);
        $this->breakTheTable();

        give(DonationTableSync::class)->flush();

        $this->assertSame('give_payment', get_post_type($donationId));
        $logs = give(LogRepository::class)->getLogsByType('error');
        $this->assertNotEmpty($logs);
        $this->assertStringContainsString('give_donations', $logs[0]->getMessage());
    }

    /**
     * @since TBD
     */
    public function testSyncChunksLargeBatches()
    {
        update_option(DonationStorageFlag::OPTION, 'posts');
        $donations = Donation::factory()->count(DonationTableSync::FLUSH_THRESHOLD + 1)->create();
        update_option(DonationStorageFlag::OPTION, 'syncing');

        give(DonationTableSync::class)->sync(...array_column($donations, 'id'));

        $this->assertSame(DonationTableSync::FLUSH_THRESHOLD + 1, (int)DB::get_var('SELECT COUNT(*) FROM ' . DB::prefix('give_donations')));
    }

    /**
     * @since TBD
     */
    public function testDeletingAnotherPostTypeLeavesTheTableAlone()
    {
        $donation = Donation::factory()->create();
        $pageId = wp_insert_post(['post_type' => 'page', 'post_title' => 'Page', 'post_status' => 'publish']);
        DB::table('give_donations')->where('id', $donation->id)->update(['id' => $pageId]);

        wp_delete_post($pageId, true);

        $this->assertNotNull($this->row($pageId));
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
     * The WordPress test suite turns CREATE TABLE and DROP TABLE into their TEMPORARY forms, and
     * the temporary table lives on across tests on the same connection, so after a test that broke
     * it the table is rebuilt from scratch.
     *
     * @since TBD
     */
    public function tearDown(): void
    {
        DB::query('DROP TABLE ' . DB::prefix('give_donations'));
        (new CreateDonationsTable())->run();

        parent::tearDown();
    }

    /**
     * Makes every write to the table fail. Dropping the table only peels off the per-test
     * temporary copy, and WordPress strips strict SQL mode so a NOT NULL column is silently
     * defaulted, so a column every INSERT names is removed instead.
     *
     * @since TBD
     */
    private function breakTheTable(): void
    {
        DB::query('ALTER TABLE ' . DB::prefix('give_donations') . ' DROP COLUMN email');
    }

    /**
     * @since TBD
     */
    private function row(int $donationId)
    {
        return DB::get_row(DB::prepare('SELECT * FROM ' . DB::prefix(DonationTableRepository::TABLE) . ' WHERE id = %d', $donationId));
    }
}
