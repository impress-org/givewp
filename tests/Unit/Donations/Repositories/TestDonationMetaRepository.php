<?php

namespace Give\Tests\Unit\Donations\Repositories;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationMetaRepository;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class TestDonationMetaRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertShouldNotDuplicateMeta(): void
    {
        $donation = Donation::factory()->create();
        $repository = new DonationMetaRepository();

        $repository->upsert($donation->id, 'test_key', 'Test Value One');
        $repository->upsert($donation->id, 'test_key', 'Test Value Two');

        $meta = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'test_key')
            ->getAll();

        $this->assertCount(1, $meta);
        $this->assertEquals('Test Value Two', $meta[0]->meta_value);
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertShouldInsertNewMeta(): void
    {
        $donation = Donation::factory()->create();
        $repository = new DonationMetaRepository();
        $repository->upsert($donation->id, 'test_key', 'test_value');

        $meta = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'test_key')
            ->get()
            ->meta_value;

        $this->assertEquals('test_value', $meta);


    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertShouldUpdateExistingMeta(): void
    {
        $donation = Donation::factory()->create();
        $repository = new DonationMetaRepository();

        DB::table('give_donationmeta')
            ->insert([
                'donation_id' => $donation->id,
                'meta_key' => 'test_key',
                'meta_value' => 'Test Value One',
            ]);

        $repository->upsert($donation->id, 'test_key', 'Test Value Two');

        $meta = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'test_key')
            ->get()
            ->meta_value;

        $this->assertEquals('Test Value Two', $meta);
    }
}
