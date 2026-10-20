<?php

namespace Give\Tests\Unit\Donations\Repositories;

use Give\Donations\Migrations\CreateDonationsTable;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationTableRepository;
use Give\Donations\Storage\DonationStorageFlag;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * The repository is exercised on its own here: donations are created while the flag is posts,
 * so DonationRepository writes no rows, then the flag is switched and each verb is called
 * directly.
 *
 * @since TBD
 */
class TestDonationTableRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
        delete_option(DonationStorageFlag::OPTION);
        (new CreateDonationsTable())->run();
    }

    /**
     * @since TBD
     */
    public function testInsertWritesOneRowPerDonation()
    {
        $donations = $this->donationsWithoutRows(3);

        (new DonationTableRepository())->insert(...$donations);

        $this->assertSame(3, $this->rowCount());
        foreach ($donations as $donation) {
            $this->assertSame((int)$donation->amount->formatToMinorAmount(), (int)$this->row($donation->id)->amount_total);
        }
    }

    /**
     * @since TBD
     */
    public function testInsertIsAnUpsert()
    {
        [$donation] = $this->donationsWithoutRows(1);
        $repository = new DonationTableRepository();

        $repository->insert($donation);
        $donation->amount = new Money(9900, 'USD');
        $repository->insert($donation);

        $this->assertSame(1, $this->rowCount());
        $this->assertSame(9900, (int)$this->row($donation->id)->amount_total);
    }

    /**
     * @since TBD
     */
    public function testUpdateRewritesTheRow()
    {
        [$donation] = $this->donationsWithoutRows(1);
        $repository = new DonationTableRepository();
        $repository->insert($donation);

        $donation->status = DonationStatus::REFUNDED();
        $donation->firstName = 'Grace';
        $repository->update($donation);

        $row = $this->row($donation->id);
        $this->assertSame('refunded', $row->status);
        $this->assertSame('Grace', $row->first_name);
    }

    /**
     * @since TBD
     */
    public function testTrashAndRestoreChangeOnlyTheStatus()
    {
        [$donation] = $this->donationsWithoutRows(1);
        $repository = new DonationTableRepository();
        $repository->insert($donation);
        $before = (array)$this->row($donation->id);

        $repository->trash($donation);
        $this->assertSame('trash', $this->row($donation->id)->status);

        $repository->restore($donation, DonationStatus::PENDING());
        $after = (array)$this->row($donation->id);

        $this->assertSame('pending', $after['status']);
        unset($before['status'], $before['updated_at'], $after['status'], $after['updated_at']);
        $this->assertSame($before, $after);
    }

    /**
     * @since TBD
     */
    public function testRestoringToTheLegacyRenewalStatusStoresComplete()
    {
        [$donation] = $this->donationsWithoutRows(1);
        $repository = new DonationTableRepository();
        $repository->insert($donation);

        $repository->restore($donation, DonationStatus::RENEWAL());

        $this->assertSame('publish', $this->row($donation->id)->status);
    }

    /**
     * @since TBD
     */
    public function testRenewalRowHoldsCompleteAndRenewal()
    {
        update_option(DonationStorageFlag::OPTION, 'posts');
        $subscription = Subscription::factory()->createWithDonation();
        $renewal = Donation::factory()->create([
            'donorId' => $subscription->donorId,
            'subscriptionId' => $subscription->id,
            'type' => DonationType::RENEWAL(),
            'status' => DonationStatus::COMPLETE(),
        ]);
        update_option(DonationStorageFlag::OPTION, 'syncing');

        (new DonationTableRepository())->insert($renewal);

        $row = $this->row($renewal->id);
        $this->assertSame('publish', $row->status);
        $this->assertSame('renewal', $row->type);
        $this->assertSame($subscription->id, (int)$row->subscription_id);
    }

    /**
     * @since TBD
     */
    public function testDeleteRemovesTheRow()
    {
        [$keep, $remove] = $this->donationsWithoutRows(2);
        $repository = new DonationTableRepository();
        $repository->insert($keep, $remove);

        $repository->delete($remove);

        $this->assertNull($this->row($remove->id));
        $this->assertNotNull($this->row($keep->id));
    }

    /**
     * @since TBD
     */
    public function testEveryVerbIsANoOpWhileStorageIsPosts()
    {
        [$donation] = $this->donationsWithoutRows(1);
        $repository = new DonationTableRepository();
        update_option(DonationStorageFlag::OPTION, 'posts');

        $repository->insert($donation);
        $repository->update($donation);
        $repository->trash($donation);
        $repository->restore($donation, DonationStatus::PENDING());
        $repository->delete($donation);

        $this->assertSame(0, $this->rowCount());
    }

    /**
     * Donations created through the model while the flag is posts, so no row exists yet.
     *
     * @since TBD
     *
     * @return Donation[]
     */
    private function donationsWithoutRows(int $count): array
    {
        update_option(DonationStorageFlag::OPTION, 'posts');
        $donations = [];
        for ($i = 0; $i < $count; $i++) {
            $donations[] = Donation::factory()->create();
        }
        update_option(DonationStorageFlag::OPTION, 'syncing');

        $this->assertSame(0, $this->rowCount());

        return $donations;
    }

    /**
     * @since TBD
     */
    private function row(int $donationId)
    {
        return DB::get_row(DB::prepare('SELECT * FROM ' . DB::prefix(DonationTableRepository::TABLE) . ' WHERE id = %d', $donationId));
    }

    /**
     * @since TBD
     */
    private function rowCount(): int
    {
        return (int)DB::get_var('SELECT COUNT(*) FROM ' . DB::prefix(DonationTableRepository::TABLE));
    }
}
