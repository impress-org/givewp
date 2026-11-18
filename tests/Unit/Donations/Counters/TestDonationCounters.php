<?php

namespace Give\Tests\Unit\Donations\Counters;

use Give\Donations\Endpoints\DonationActions;
use Give\Donations\Endpoints\ListDonations;
use Give\Donations\Endpoints\ListDonationsStats;
use Give\Donations\Counters\SyncDonationCounters;
use Give\Donations\Models\Donation;
use Give\Donations\Counters\DonationCounters;
use Give\Donations\Counters\DonationCountersRebuild;
use Give\Donations\Counters\DonationCountersSchedule;
use Give\Donations\Counters\DonationTotals;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Helper_Payment;
use WP_REST_Request;
use WP_REST_Server;

/**
 * The donation counters must equal a direct recount after every write path, and the stats and list
 * endpoints must show the new numbers right after the write, through the counters and through the
 * computed totals alike.
 *
 * @since TBD
 */
class TestDonationCounters extends TestCase
{
    use RefreshDatabase;

    /**
     * @var callable[] filters added by a test, removed in tearDown
     */
    private $filters = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        // The trait truncates after each test; a run that stopped early leaves rows behind.
        $this->refreshDatabase();
        $this->deleteCounterRows();
        $this->deleteTotalTransients();
        give(SyncDonationCounters::class)->reset();
        DonationCountersSchedule::reset();
        as_unschedule_all_actions(DonationCountersSchedule::RECOUNT_HOOK);
        as_unschedule_all_actions(DonationCountersSchedule::REBUILD_HOOK);

        $user = wp_get_current_user();
        $user->add_cap('delete_give_payments');
        $user->add_cap('edit_give_payments');
        $user->add_cap('view_give_payments');

        $this->rebuild();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach ($this->filters as list($hook, $callback)) {
            remove_filter($hook, $callback, 99);
        }

        $this->filters = [];
        give(SyncDonationCounters::class)->reset();

        parent::tearDown();
    }

    // -----------------------------------------------------------------------------------------
    // Creates
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function testModelCreateCountsLiveAndTestDonations(): void
    {
        Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        $this->assertBucket('live_onetime_publish', 1);

        Donation::factory()->create(['status' => DonationStatus::PENDING(), 'mode' => DonationMode::TEST()]);
        $this->assertBucket('test_onetime_pending', 1);

        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testModelCreateOfRecurringDonationCountsAsRecurring(): void
    {
        Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE(), 'type' => DonationType::SUBSCRIPTION(), 'subscriptionId' => 7]);

        $this->assertBucket('live_recurring_publish', 1);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testLegacyGiveInsertPaymentCreateIsCountedOnce(): void
    {
        $paymentId = Give_Helper_Payment::create_simple_payment();

        $this->assertGreaterThan(0, $paymentId);
        $this->assertSame(1, array_sum(DonationCounters::read()['buckets']));
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testLegacyCreateInTestModeLandsInTheTestBucket(): void
    {
        $paymentId = Give_Helper_Payment::create_simple_payment();
        give_update_meta($paymentId, DonationMetaKeys::MODE, DonationMode::TEST);

        $this->assertBucket('test_onetime_pending', 1);
        $this->assertBucket('live_onetime_pending', 0);
        $this->assertExactEverywhere();
    }

    // -----------------------------------------------------------------------------------------
    // Status changes
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function statusProvider(): array
    {
        return [
            'pending' => [DonationStatus::PENDING],
            'complete' => [DonationStatus::COMPLETE],
            'refunded' => [DonationStatus::REFUNDED],
            'failed' => [DonationStatus::FAILED],
            'cancelled' => [DonationStatus::CANCELLED],
            'abandoned' => [DonationStatus::ABANDONED],
            'processing' => [DonationStatus::PROCESSING],
            'preapproval' => [DonationStatus::PREAPPROVAL],
            'revoked' => [DonationStatus::REVOKED],
        ];
    }

    /**
     * @since TBD
     *
     * @dataProvider statusProvider
     */
    public function testModelStatusChangeMovesTheDonation(string $status): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PROCESSING(), 'mode' => DonationMode::TEST()]);

        $donation->status = new DonationStatus($status);
        $donation->save();

        $this->assertBucket("test_onetime_{$status}", 1);
        $this->assertExactEverywhere();
    }

    /**
     * Gateways (Stripe webhooks included) change statuses through give_update_payment_status().
     *
     * @since TBD
     *
     * @dataProvider statusProvider
     */
    public function testLegacyStatusChangeMovesTheDonation(string $status): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PROCESSING(), 'mode' => DonationMode::LIVE()]);
        give(SyncDonationCounters::class)->reset();

        give_update_payment_status($donation->id, $status);

        $this->assertBucket("live_onetime_{$status}", 1);
        $this->assertExactEverywhere();
    }

    // -----------------------------------------------------------------------------------------
    // Trash, untrash, delete
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function testModelTrashAndUntrash(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);

        $donation->trash();
        $this->assertBucket('live_onetime_trash', 1);
        $this->assertBucket('live_onetime_publish', 0);
        $this->assertExactEverywhere();

        $donation->unTrash();
        $this->assertBucket('live_onetime_publish', 1);
        $this->assertBucket('live_onetime_trash', 0);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testWordPressTrashAndUntrash(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);
        give(SyncDonationCounters::class)->reset();

        wp_trash_post($donation->id);
        $this->assertBucket('test_onetime_trash', 1);
        $this->assertExactEverywhere();

        give(SyncDonationCounters::class)->reset();
        wp_untrash_post($donation->id);
        $this->assertBucket('test_onetime_trash', 0);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testModelDelete(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        give(SyncDonationCounters::class)->reset();

        $donation->delete();

        $this->assertSame(0, array_sum(DonationCounters::read()['buckets']));
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testWpDeletePost(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::REFUNDED(), 'mode' => DonationMode::TEST()]);
        give(SyncDonationCounters::class)->reset();

        wp_delete_post($donation->id, true);

        $this->assertBucket('test_onetime_refunded', 0);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testLegacyGiveDeleteDonation(): void
    {
        $paymentId = Give_Helper_Payment::create_simple_payment();
        give(SyncDonationCounters::class)->reset();

        give_delete_donation($paymentId);

        $this->assertSame(0, array_sum(DonationCounters::read()['buckets']));
        $this->assertExactEverywhere();
    }

    // -----------------------------------------------------------------------------------------
    // Mode and subscription changes
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function testModelModeSwitchLiveToTestAndBack(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        give(SyncDonationCounters::class)->reset();

        $donation->mode = DonationMode::TEST();
        $donation->save();
        $this->assertBucket('test_onetime_publish', 1);
        $this->assertBucket('live_onetime_publish', 0);
        $this->assertExactEverywhere();

        give(SyncDonationCounters::class)->reset();
        $donation->mode = DonationMode::LIVE();
        $donation->save();
        $this->assertBucket('live_onetime_publish', 1);
        $this->assertBucket('test_onetime_publish', 0);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testMetaApiModeSwitchAndModeDelete(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING(), 'mode' => DonationMode::LIVE()]);
        give(SyncDonationCounters::class)->reset();

        give_update_meta($donation->id, DonationMetaKeys::MODE, DonationMode::TEST);
        $this->assertBucket('test_onetime_pending', 1);
        $this->assertExactEverywhere();

        give(SyncDonationCounters::class)->reset();
        give_delete_meta($donation->id, DonationMetaKeys::MODE);
        $this->assertBucket('live_onetime_pending', 1);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testModelSubscriptionIdSetAndCleared(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST(), 'subscriptionId' => 0]);
        give(SyncDonationCounters::class)->reset();

        $donation->type = DonationType::SUBSCRIPTION();
        $donation->subscriptionId = 12;
        $donation->save();
        $this->assertBucket('test_recurring_publish', 1);
        $this->assertExactEverywhere();

        give(SyncDonationCounters::class)->reset();
        $donation->type = DonationType::SINGLE();
        $donation->subscriptionId = 0;
        $donation->save();
        $this->assertBucket('test_onetime_publish', 1);
        $this->assertBucket('test_recurring_publish', 0);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testMetaApiSubscriptionIdSetAndDeleted(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE(), 'subscriptionId' => 0]);
        give(SyncDonationCounters::class)->reset();

        give_update_meta($donation->id, DonationMetaKeys::SUBSCRIPTION_ID, 33);
        $this->assertBucket('live_recurring_publish', 1);
        $this->assertExactEverywhere();

        give(SyncDonationCounters::class)->reset();
        give_delete_meta($donation->id, DonationMetaKeys::SUBSCRIPTION_ID);
        $this->assertBucket('live_onetime_publish', 1);
        $this->assertExactEverywhere();
    }

    // -----------------------------------------------------------------------------------------
    // Bulk actions
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function testBulkActionsSetStatusTrashUntrashDelete(): void
    {
        $ids = array_map(static function (Donation $donation) {
            return $donation->id;
        }, Donation::factory()->count(4)->create(['status' => DonationStatus::PENDING(), 'mode' => DonationMode::TEST()]));
        give(SyncDonationCounters::class)->reset();

        $this->bulk('setStatus', $ids, ['status' => DonationStatus::COMPLETE]);
        $this->assertBucket('test_onetime_publish', 4);
        $this->assertExactEverywhere();

        $this->bulk('trash', array_slice($ids, 0, 2));
        $this->assertBucket('test_onetime_trash', 2);
        $this->assertExactEverywhere();

        $this->bulk('untrash', array_slice($ids, 0, 1));
        $this->assertBucket('test_onetime_trash', 1);
        $this->assertExactEverywhere();

        $this->bulk('delete', array_slice($ids, 1, 3));
        $this->assertBucket('test_onetime_publish', 1);
        $this->assertBucket('test_onetime_trash', 0);
        $this->assertExactEverywhere();
    }

    // -----------------------------------------------------------------------------------------
    // Idempotency, errors, many writes
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function testDuplicateEventsInOneRequestDoNotDoubleCount(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING(), 'mode' => DonationMode::LIVE()]);
        $post = get_post($donation->id);

        do_action('givewp_donation_created', $donation);
        do_action('give_insert_payment', $donation->id, ['give_form_id' => $donation->formId]);
        do_action('givewp_donation_updated', $donation);
        do_action('give_update_payment_status', $donation->id, 'pending', 'publish');

        $this->assertBucket('live_onetime_pending', 1);
        $this->assertExactEverywhere();

        $donation->status = DonationStatus::COMPLETE();
        $donation->save();
        do_action('givewp_donation_updated', $donation);
        do_action('give_update_payment_status', $donation->id, 'publish', 'pending');

        $this->assertBucket('live_onetime_publish', 1);
        $this->assertBucket('live_onetime_pending', 0);
        $this->assertExactEverywhere();

        wp_delete_post($donation->id, true);
        do_action('deleted_post', $donation->id, $post);
        do_action('give_payment_deleted', $donation->id);

        $this->assertSame(0, array_sum(DonationCounters::read()['buckets']));
        $this->assertExactEverywhere();
    }

    /**
     * A legacy hook alone carries nothing that tells a new change from the same one again, so it
     * marks the counters dirty instead of moving the donation.
     *
     * @since TBD
     */
    public function testLegacyHookAloneMarksDirtyInsteadOfGuessing(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING(), 'mode' => DonationMode::LIVE()]);
        give(SyncDonationCounters::class)->reset();
        DonationCountersSchedule::reset();

        DB::query(DB::prepare("UPDATE " . DB::prefix('posts') . " SET post_status = 'publish' WHERE ID = %d", $donation->id));
        do_action('give_update_payment_status', $donation->id, 'publish', 'pending');

        $this->assertDirty();
        $this->assertBucket('live_onetime_pending', 1);
        $this->assertTrue(as_has_scheduled_action(DonationCountersSchedule::RECOUNT_HOOK));
        $this->assertScreensShowRecount();

        $this->rebuild();
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testFailingCounterUpdateKeepsTheWriteAndMarksDirty(): void
    {
        $this->addTestFilter('query', static function ($query) {
            return strpos($query, DonationCounters::BUCKET_PREFIX) !== false && stripos($query, 'INSERT') === 0
                ? 'INSERT INTO no_such_table_for_counters VALUES (1)'
                : $query;
        });

        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);

        $this->assertNotNull(Donation::find($donation->id));
        $this->assertDirty();
        $this->assertScreensShowRecount();
    }

    /**
     * @since TBD
     */
    public function testCreateThenDeleteInTheSameRequest(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);
        $donation->delete();

        $legacyId = Give_Helper_Payment::create_simple_payment();
        wp_delete_post($legacyId, true);

        $this->assertSame(0, array_sum(DonationCounters::read()['buckets']));
        $this->assertNotDirty();
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testManyWritesInOneRequest(): void
    {
        $donations = Donation::factory()->count(20)->create(['status' => DonationStatus::PENDING(), 'mode' => DonationMode::TEST()]);

        foreach ($donations as $index => $donation) {
            $donation->status = $index % 2 ? DonationStatus::COMPLETE() : DonationStatus::FAILED();
            $donation->save();
        }

        foreach (array_slice($donations, 0, 5) as $donation) {
            $donation->delete();
        }

        foreach (array_slice($donations, 5, 3) as $donation) {
            $donation->trash();
        }

        $this->assertNotDirty();
        $this->assertSame(15, array_sum(DonationCounters::read()['buckets']));
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testWritesOfTheOtherModeLeaveThisModeAlone(): void
    {
        Donation::factory()->count(3)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        $live = DonationCounters::statistics(false);

        $test = Donation::factory()->create(['status' => DonationStatus::PENDING(), 'mode' => DonationMode::TEST()]);
        $test->status = DonationStatus::REFUNDED();
        $test->save();
        $test->trash();
        $test->delete();

        $this->assertSame($live, DonationCounters::statistics(false));
        $this->assertExactEverywhere();
    }

    // -----------------------------------------------------------------------------------------
    // Trust: missing, dirty, wrong counters
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function testMissingCountersFallBackAndScheduleTheFirstRebuild(): void
    {
        Donation::factory()->count(2)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);
        $this->deleteCounterRows();
        DonationCountersSchedule::reset();

        $this->assertNull(DonationCounters::statistics(true));
        $this->assertScreensShowRecount();
        $this->assertTrue(as_has_scheduled_action(DonationCountersSchedule::RECOUNT_HOOK));

        $this->rebuild();
        $this->assertNotNull(DonationCounters::statistics(true));
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testDirtyCountersAreNotUsed(): void
    {
        Donation::factory()->count(2)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        DonationCounters::add([DonationCounters::bucket('live', 'onetime', 'publish') => 40]);
        DonationCounters::markDirty();

        $this->assertNull(DonationCounters::statistics(false));
        $this->assertScreensShowRecount();
    }

    /**
     * @since TBD
     */
    public function testNegativeBucketIsNotUsedAndMarksDirty(): void
    {
        DonationCounters::add([DonationCounters::bucket('live', 'onetime', 'publish') => -1]);

        $this->assertNull(DonationCounters::total(false));
        $this->assertDirty();
    }

    /**
     * @since TBD
     */
    public function testRebuildRepairsDriftFromARawSqlInsert(): void
    {
        Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);
        $id = $this->rawInsertDonation('publish', DonationMode::TEST);

        $this->assertBucket('test_onetime_publish', 1);

        $result = $this->rebuild();

        $this->assertSame('exact', $result['status']);
        $this->assertSame([DonationCounters::bucket('test', 'onetime', 'publish') => 1], $result['changed']);
        $this->assertBucket('test_onetime_publish', 2);
        $this->assertGreaterThan(0, $id);
        $this->assertExactEverywhere();
    }

    /**
     * Before the rebuild, a page read that contradicts the counter fixes the displayed total and
     * marks the counters dirty.
     *
     * @since TBD
     */
    public function testPageReadCorrectsACounterThatIsTooLowAndMarksDirty(): void
    {
        Donation::factory()->count(3)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);
        $this->rawInsertDonation('publish', DonationMode::TEST);
        $this->rawInsertDonation('publish', DonationMode::TEST);

        $response = $this->listResponse(true, 1, 30);

        $this->assertSame(5, $response['totalItems']);
        $this->assertCount(5, $response['items']);
        $this->assertDirty();
    }

    /**
     * @since TBD
     */
    public function testPageReadCorrectsACounterThatIsTooHigh(): void
    {
        Donation::factory()->count(2)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        DonationCounters::add([DonationCounters::bucket('live', 'onetime', 'publish') => 10]);

        $response = $this->listResponse(false, 1, 30);

        $this->assertSame(2, $response['totalItems']);
        $this->assertDirty();
    }

    /**
     * A deep page counts live; a live count that differs from an unchanged counter marks it dirty,
     * and the response carries the live total.
     *
     * @since TBD
     */
    public function testDeepPageLiveCountMarksAWrongCounterDirty(): void
    {
        Donation::factory()->count(10)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);
        $this->rawInsertDonation('publish', DonationMode::TEST);
        $this->rawInsertDonation('publish', DonationMode::TEST);

        $response = $this->listResponse(true, 4, 3);

        $this->assertSame(12, $response['totalItems']);
        $this->assertCount(3, $response['items']);
        $this->assertDirty();
    }

    /**
     * A deep page whose live count agrees with the counter leaves it clean.
     *
     * @since TBD
     */
    public function testDeepPageLiveCountKeepsARightCounterClean(): void
    {
        Donation::factory()->count(10)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);

        $this->assertSame(10, $this->listResponse(true, 4, 3)['totalItems']);
        $this->assertNotDirty();
    }

    /**
     * Rows are chosen from a live count, never from a counter or a cached total: with counters that
     * are too high or too low, every page still holds exactly the expected donations.
     *
     * @since TBD
     */
    public function testRowsAreNeverChosenFromACounter(): void
    {
        $ids = [];

        foreach (Donation::factory()->count(10)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]) as $donation) {
            $ids[] = $donation->id;
        }

        rsort($ids);

        foreach ([-6, 7] as $error) {
            $this->rebuild();
            DonationCounters::add([DonationCounters::bucket('test', 'onetime', 'publish') => $error]);

            $seen = [];

            for ($page = 1; $page <= 4; $page++) {
                $response = $this->listResponse(true, $page, 3);
                $pageIds = array_map('intval', array_column($response['items'], 'id'));

                $this->assertSame(array_slice($ids, ($page - 1) * 3, 3), $pageIds, "error {$error}, page {$page}");
                $seen = array_merge($seen, $pageIds);
            }

            $this->assertSame($ids, $seen);
        }
    }

    // -----------------------------------------------------------------------------------------
    // Rebuild races
    // -----------------------------------------------------------------------------------------

    /**
     * Writes from another connection while the rebuild counts are kept: the rebuild adds its
     * correction to the counters instead of replacing them.
     *
     * @since TBD
     */
    public function testRebuildKeepsWritesThatLandWhileItCounts(): void
    {
        Donation::factory()->count(2)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        $this->rawInsertDonation('publish', DonationMode::LIVE);

        $created = [];
        $rebuild = new DonationCountersRebuild(1, 0, function () use (&$created) {
            $this->onAnotherConnection(function () use (&$created) {
                $created[] = Donation::factory()->create(['status' => DonationStatus::PENDING(), 'mode' => DonationMode::LIVE()])->id;
                $created[] = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()])->id;
            });
        });

        $result = $rebuild();

        $this->assertSame('exact', $result['status']);
        $this->assertCount(2, $created);
        $this->assertBucket('live_onetime_publish', 3);
        $this->assertBucket('live_onetime_pending', 1);
        $this->assertBucket('test_onetime_publish', 1);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testRebuildWaitsForALiveWriterAndMarksDirtyWhenItNeverFinishes(): void
    {
        $this->insertWritingRow(time());

        $result = (new DonationCountersRebuild(3, 0))();

        $this->assertSame('inexact', $result['status']);
        $this->assertSame(3, $result['attempts']);
        $this->assertDirty();
    }

    /**
     * @since TBD
     */
    public function testRebuildIgnoresAndRemovesAnExpiredWriter(): void
    {
        $name = $this->insertWritingRow(time() - DonationCountersRebuild::WRITING_EXPIRES_AFTER - 10);

        $result = (new DonationCountersRebuild(3, 0))();

        $this->assertSame('exact', $result['status']);
        $this->assertSame(1, $result['attempts']);
        $this->assertNull(DB::get_var(DB::prepare('SELECT option_name FROM ' . DB::prefix('options') . ' WHERE option_name = %s', $name)));
    }

    /**
     * @since TBD
     */
    public function testWriteReleasesItsWritingRow(): void
    {
        Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        Give_Helper_Payment::create_simple_payment();

        $this->assertSame(0, $this->countWritingRows());
    }

    /**
     * @since TBD
     */
    public function testRebuildRunsOneAtATime(): void
    {
        $lock = new \Give\Donations\Counters\DonationTotalsLock(DonationCountersRebuild::LOCK);
        $lock->acquire();

        try {
            $this->assertSame('busy', (new DonationCountersRebuild())()['status']);
        } finally {
            $lock->release();
        }
    }

    // -----------------------------------------------------------------------------------------
    // Donation storage flag
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function testCountersAreOffAndDoNotCountOnTableStorage(): void
    {
        update_option('give_donation_storage', 'table');

        try {
            $this->assertFalse(DonationCounters::isEnabled());
            $this->assertTrue(DonationCounters::usesTableStorage());

            $before = DonationCounters::read();
            Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);

            $this->assertSame($before['buckets'], DonationCounters::read()['buckets']);
        } finally {
            delete_option('give_donation_storage');
        }
    }

    /**
     * Only the syncing state is testable here: the table sync class is not in this branch, so a
     * test that runs the counters together with it cannot be written yet.
     *
     * @since TBD
     */
    public function testCountersStayOnWhileStorageIsSyncing(): void
    {
        update_option('give_donation_storage', 'syncing');

        try {
            $this->assertTrue(DonationCounters::isEnabled());

            Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);

            $this->assertBucket('live_onetime_publish', 1);
        } finally {
            delete_option('give_donation_storage');
        }
    }

    // -----------------------------------------------------------------------------------------
    // Filters
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function intervalProvider(): array
    {
        return [
            'default' => [null, 3600],
            'numeric string' => ['7200', 7200],
            'too low is raised' => [5, 60],
            'zero turns it off' => [0, 0],
            'negative turns it off' => [-30, 0],
            'not a number falls back' => ['hourly', 3600],
            'array falls back' => [[1], 3600],
        ];
    }

    /**
     * @since TBD
     *
     * @dataProvider intervalProvider
     *
     * @param mixed $value
     */
    public function testRebuildIntervalFilter($value, int $expected): void
    {
        if ($value !== null) {
            $this->addTestFilter('givewp_donation_counters_rebuild_interval', static function () use ($value) {
                return $value;
            });
        }

        $this->assertSame($expected, DonationCountersSchedule::interval());
    }

    /**
     * The interval is read on every scheduling decision: a change replaces the scheduled action, and
     * 0 removes it.
     *
     * @since TBD
     */
    public function testScheduledRebuildFollowsTheIntervalFilter(): void
    {
        $interval = 120;
        $this->addTestFilter('givewp_donation_counters_rebuild_interval', static function () use (&$interval) {
            return $interval;
        });

        DonationCountersSchedule::ensureScheduled(0.0, true);
        $this->assertSame([120], $this->scheduledIntervals());

        $interval = 900;
        DonationCountersSchedule::ensureScheduled(0.0, true);
        $this->assertSame([900], $this->scheduledIntervals());

        $interval = 0;
        DonationCountersSchedule::ensureScheduled(0.0, true);
        $this->assertSame([], $this->scheduledIntervals());
    }

    /**
     * @since TBD
     */
    public function testTurningCountersOffUsesTheComputedTotalsAndRecountsWhenTurnedBackOn(): void
    {
        $enabled = false;
        $this->addTestFilter('givewp_donation_counters_enabled', static function () use (&$enabled) {
            return $enabled;
        });

        Donation::factory()->count(2)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);

        $this->assertNull(DonationCounters::statistics(true));
        $this->assertScreensShowRecount();
        $this->assertBucket('test_onetime_publish', 0);

        DonationCountersSchedule::ensureScheduled(0.0, true);
        $this->assertSame([], $this->scheduledIntervals());

        $enabled = true;
        $this->assertNull(DonationCounters::statistics(true), 'Writes made while off must be recounted first.');

        $this->rebuild();
        $this->assertExactEverywhere();
    }

    // -----------------------------------------------------------------------------------------
    // Review fixes
    // -----------------------------------------------------------------------------------------

    /**
     * @since TBD
     */
    public function testAWriteReadsTheBucketAgainWhenAnotherRequestMovedTheDonation(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);

        // Another request moves the donation to refunded and moves the counters with it.
        global $wpdb;
        $wpdb->update($wpdb->posts, ['post_status' => 'refunded'], ['ID' => $donation->id]);
        DonationCounters::add([
            DonationCounters::bucket('live', 'onetime', 'publish') => -1,
            DonationCounters::bucket('live', 'onetime', 'refunded') => 1,
        ]);
        clean_post_cache($donation->id);

        // This request still remembers the donation as complete, and writes it again.
        $donation = Donation::find($donation->id);
        $donation->status = DonationStatus::PENDING();
        $donation->save();

        $this->assertBucket('live_onetime_publish', 0);
        $this->assertBucket('live_onetime_refunded', 0);
        $this->assertBucket('live_onetime_pending', 1);
        $this->assertExactEverywhere();
    }

    /**
     * @since TBD
     */
    public function testAThrowingEnabledFilterDoesNotFailTheWrite(): void
    {
        $this->addTestFilter('givewp_donation_counters_enabled', static function () {
            throw new \RuntimeException('filter failed');
        });

        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        $donation->status = DonationStatus::REFUNDED();
        $donation->save();
        give(SyncDonationCounters::class)->releaseAll();

        $this->assertNotNull(Donation::find($donation->id));
        $this->assertSame('refunded', Donation::find($donation->id)->status->getValue());
    }

    /**
     * @since TBD
     */
    public function testTheLiveListCountIsOneStatementWithTheCountersOff(): void
    {
        $this->addTestFilter('givewp_donation_counters_enabled', '__return_false');
        $this->addTestFilter('givewp_donation_totals_ttl', '__return_zero');
        Donation::factory()->count(2)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        Donation::factory()->count(3)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST()]);

        $queries = $this->recordQueries(function () use (&$response) {
            $response = $this->listResponse(false, 1, 30);
        });

        $this->assertSame(2, (int)$response['totalItems']);
        $this->assertCount(1, array_filter($queries, static function ($query) {
            return stripos($query, 'COUNT(') !== false;
        }), 'both counts must be read by one statement');
    }

    /**
     * @since TBD
     */
    public function testTheStatisticsAreOneStatementWithTheCountersOff(): void
    {
        $this->addTestFilter('givewp_donation_counters_enabled', '__return_false');
        $this->addTestFilter('givewp_donation_totals_ttl', '__return_zero');
        Donation::factory()->count(2)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::LIVE()]);
        Donation::factory()->count(3)->create(['status' => DonationStatus::COMPLETE(), 'mode' => DonationMode::TEST(), 'type' => DonationType::SUBSCRIPTION(), 'subscriptionId' => 4]);

        $queries = $this->recordQueries(function () use (&$live, &$test) {
            $live = $this->statistics(false);
            $test = $this->statistics(true);
        });

        $this->assertSame(['donationsCount' => 2, 'oneTimeDonationsCount' => 2, 'recurringDonationsCount' => 0], $live);
        $this->assertSame(['donationsCount' => 3, 'oneTimeDonationsCount' => 0, 'recurringDonationsCount' => 3], $test);
        $this->assertCount(2, array_filter($queries, static function ($query) {
            return stripos($query, 'COUNT(') !== false;
        }), 'each mode reads its counts with one statement');
    }

    /**
     * The statements run while the callback runs.
     *
     * @since TBD
     *
     * @return string[]
     */
    private function recordQueries(callable $callback): array
    {
        $queries = [];
        $record = static function ($query) use (&$queries) {
            $queries[] = $query;

            return $query;
        };

        add_filter('query', $record, 1000);

        try {
            $callback();
        } finally {
            remove_filter('query', $record, 1000);
        }

        return $queries;
    }

    // -----------------------------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------------------------

    /**
     * Counters equal a direct recount, are used, and both screens (through the counters and through
     * the computed totals with the transient off) show the recount.
     *
     * @since TBD
     */
    private function assertExactEverywhere(): void
    {
        $this->assertNotDirty();
        $this->assertSame($this->recount(), $this->nonZero(DonationCounters::read()['buckets']), 'counters vs direct recount');
        $this->assertNotNull(DonationCounters::statistics(true), 'counters are used');
        $this->assertScreensShowRecount();
    }

    /**
     * The stats and list endpoints show the recount, with the counters as they are and with the
     * counters off and the transient off.
     *
     * @since TBD
     */
    private function assertScreensShowRecount(): void
    {
        $recount = $this->recount();

        foreach ([true, false] as $testMode) {
            $expected = $this->expectedStatistics($recount, $testMode);

            $this->assertSame($expected, $this->statistics($testMode), 'stats, testMode ' . (int)$testMode);
            $this->assertSame($expected['donationsCount'], $this->listResponse($testMode, 1, 30)['totalItems'], 'list, testMode ' . (int)$testMode);
        }

        $off = static function () {
            return false;
        };
        $exact = static function () {
            return 0;
        };
        add_filter('givewp_donation_counters_enabled', $off, 100);
        add_filter('givewp_donation_totals_ttl', $exact, 100);

        try {
            foreach ([true, false] as $testMode) {
                $expected = $this->expectedStatistics($recount, $testMode);

                $this->assertSame($expected, $this->statistics($testMode), 'cached stats, testMode ' . (int)$testMode);
                $this->assertSame($expected['donationsCount'], $this->listResponse($testMode, 1, 30)['totalItems'], 'cached list, testMode ' . (int)$testMode);
            }
        } finally {
            remove_filter('givewp_donation_counters_enabled', $off, 100);
            remove_filter('givewp_donation_totals_ttl', $exact, 100);
        }
    }

    /**
     * @since TBD
     */
    private function expectedStatistics(array $recount, bool $testMode): array
    {
        $mode = $testMode ? 'test' : 'live';
        $total = $recurring = 0;

        foreach ($recount as $bucket => $count) {
            list($bucketMode, $type, $status) = explode('_', substr($bucket, strlen(DonationCounters::BUCKET_PREFIX)), 3);

            if ($bucketMode !== $mode || $status === 'trash') {
                continue;
            }

            $total += $count;
            $recurring += $type === 'recurring' ? $count : 0;
        }

        return ['donationsCount' => $total, 'oneTimeDonationsCount' => $total - $recurring, 'recurringDonationsCount' => $recurring];
    }

    /**
     * @since TBD
     */
    private function statistics(bool $testMode): array
    {
        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donations/stats');
        $request->set_param('testMode', $testMode);
        $data = (new ListDonationsStats())->handleRequest($request)->get_data();

        return array_intersect_key($data, array_flip(['donationsCount', 'oneTimeDonationsCount', 'recurringDonationsCount']));
    }

    /**
     * @since TBD
     */
    private function listResponse(bool $testMode, int $page, int $perPage): array
    {
        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donations');
        $request->set_param('page', $page);
        $request->set_param('perPage', $perPage);
        $request->set_param('locale', 'en-US');
        $request->set_param('testMode', $testMode);
        $request->set_param('return', 'model');

        $data = give(ListDonations::class)->handleRequest($request)->get_data();
        $data['items'] = array_map(static function (Donation $donation) {
            return ['id' => $donation->id];
        }, $data['items']);

        return $data;
    }

    /**
     * Buckets counted with plain SQL, independent of the rebuild's queries.
     *
     * @return array<string, int>
     *
     * @since TBD
     */
    private function recount(): array
    {
        $posts = DB::prefix('posts');
        $meta = DB::prefix('give_donationmeta');
        $rows = DB::get_results("SELECT IF(t.donation_id IS NULL, 'live', 'test') AS mode,
                IF(s.donation_id IS NULL, 'onetime', 'recurring') AS type, p.post_status AS status, COUNT(*) AS n
            FROM {$posts} p
            LEFT JOIN (SELECT DISTINCT donation_id FROM {$meta} WHERE meta_key = '_give_payment_mode' AND meta_value = 'test') t ON t.donation_id = p.ID
            LEFT JOIN (SELECT DISTINCT donation_id FROM {$meta} WHERE meta_key = 'subscription_id' AND meta_value >= '1') s ON s.donation_id = p.ID
            WHERE p.post_type = 'give_payment' GROUP BY 1, 2, 3");

        $buckets = [];

        foreach ($rows as $row) {
            $buckets[DonationCounters::bucket($row->mode, $row->type, $row->status)] = (int)$row->n;
        }

        ksort($buckets);

        return $buckets;
    }

    /**
     * @since TBD
     */
    private function nonZero(array $buckets): array
    {
        $buckets = array_filter($buckets);
        ksort($buckets);

        return $buckets;
    }

    /**
     * @since TBD
     */
    private function assertBucket(string $bucket, int $expected): void
    {
        $this->assertSame($expected, DonationCounters::read()['buckets'][DonationCounters::BUCKET_PREFIX . $bucket] ?? 0, $bucket);
    }

    /**
     * @since TBD
     */
    private function assertDirty(): void
    {
        $state = DonationCounters::read();
        $this->assertGreaterThan($state['clean'], $state['dirty'], 'counters should be dirty');
    }

    /**
     * @since TBD
     */
    private function assertNotDirty(): void
    {
        $state = DonationCounters::read();
        $this->assertLessThanOrEqual($state['clean'], $state['dirty'], 'counters should not be dirty');
    }

    /**
     * @since TBD
     */
    private function rebuild(): array
    {
        return (new DonationCountersRebuild(1, 0))();
    }

    /**
     * @since TBD
     */
    private function bulk(string $action, array $ids, array $params = []): void
    {
        $request = new WP_REST_Request(WP_REST_Server::DELETABLE, "/give-api/v2/admin/donations/{$action}");
        $request->set_param('action', $action);
        $request->set_param('ids', implode(',', $ids));

        foreach ($params as $key => $value) {
            $request->set_param($key, $value);
        }

        $response = give(DonationActions::class)->handleRequest($request);
        $this->assertSame(200, $response->get_status(), $action);
    }

    /**
     * Inserts a copy of the latest donation with SQL only, so no hook fires, then sets its status
     * and mode the same way.
     *
     * @since TBD
     */
    private function rawInsertDonation(string $status, string $mode): int
    {
        $posts = DB::prefix('posts');
        $meta = DB::prefix('give_donationmeta');
        $templateId = (int)DB::get_var("SELECT MAX(ID) FROM {$posts} WHERE post_type = 'give_payment'");

        DB::query(DB::prepare(
            "INSERT INTO {$posts} (post_type, post_status, post_date, post_date_gmt, post_modified, post_modified_gmt, post_content, post_title, post_excerpt, to_ping, pinged, post_content_filtered)
             VALUES ('give_payment', %s, NOW(), UTC_TIMESTAMP(), NOW(), UTC_TIMESTAMP(), '', '', '', '', '', '')",
            $status
        ));
        $id = (int)DB::last_insert_id();

        DB::query(DB::prepare(
            "INSERT INTO {$meta} (donation_id, meta_key, meta_value) SELECT %d, meta_key, meta_value FROM {$meta} WHERE donation_id = %d",
            $id,
            $templateId
        ));
        DB::query(DB::prepare("UPDATE {$meta} SET meta_value = %s WHERE donation_id = %d AND meta_key = '_give_payment_mode'", $mode, $id));

        return $id;
    }

    /**
     * Runs the callback with a second database connection as $wpdb, so its writes commit on their
     * own while the first connection holds a snapshot.
     *
     * @since TBD
     */
    private function onAnotherConnection(callable $callback): void
    {
        global $wpdb;

        $original = $wpdb;
        // A copy keeps every table name Give registered; it must not share the connection or the
        // last result with the original.
        $other = clone $original;
        $other->result = null;
        $other->dbh = null;
        $other->db_connect(false);
        $wpdb = $other;

        try {
            $callback();
        } finally {
            $wpdb = $original;
            $other->close();
        }
    }

    /**
     * @since TBD
     */
    private function insertWritingRow(int $time): string
    {
        $name = DonationCounters::WRITING_PREFIX . 'test' . $time;
        DB::query(DB::prepare(
            'INSERT INTO ' . DB::prefix('options') . " (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
            $name,
            (string)$time
        ));

        return $name;
    }

    /**
     * @since TBD
     */
    private function countWritingRows(): int
    {
        return (int)DB::get_var(DB::prepare(
            'SELECT COUNT(*) FROM ' . DB::prefix('options') . ' WHERE option_name LIKE %s',
            DB::esc_like(DonationCounters::WRITING_PREFIX) . '%'
        ));
    }

    /**
     * @since TBD
     */
    private function scheduledIntervals(): array
    {
        $intervals = [];

        foreach (as_get_scheduled_actions(['hook' => DonationCountersSchedule::REBUILD_HOOK, 'status' => 'pending'], 'ids') as $id) {
            $intervals[] = (int)\ActionScheduler::store()->fetch_action($id)->get_schedule()->get_recurrence();
        }

        return $intervals;
    }

    /**
     * @since TBD
     */
    private function addTestFilter(string $hook, callable $callback): void
    {
        add_filter($hook, $callback, 99);
        $this->filters[] = [$hook, $callback];
    }

    /**
     * @since TBD
     */
    private function deleteTotalTransients(): void
    {
        DB::query(DB::prepare(
            'DELETE FROM ' . DB::prefix('options') . ' WHERE option_name LIKE %s OR option_name LIKE %s',
            DB::esc_like('_transient_' . DonationTotals::TRANSIENT_PREFIX) . '%',
            DB::esc_like('_transient_timeout_' . DonationTotals::TRANSIENT_PREFIX) . '%'
        ));
        wp_cache_flush();
    }

    /**
     * @since TBD
     */
    private function deleteCounterRows(): void
    {
        DB::query(DB::prepare(
            'DELETE FROM ' . DB::prefix('options') . ' WHERE option_name LIKE %s',
            DB::esc_like('give_donation_count') . '%'
        ));
    }
}
