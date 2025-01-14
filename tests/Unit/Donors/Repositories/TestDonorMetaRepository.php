<?php

namespace Give\Tests\Unit\Donors\Repositories;

use Exception;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorMetaRepository;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @coversDefaultClass \Give\Donors\Repositories\DonorMetaRepository
 */
class TestDonorMetaRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertMetaShouldInsertNewMeta(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();
        $repository->upsert($donor->id, 'test_key', 'test_value');

        $meta = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', 'test_key')
            ->get()
            ->meta_value;

        $this->assertEquals('test_value', $meta);
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertMetaShouldNotDuplicateMeta(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();
        $repository->upsert($donor->id, 'test_key', 'Test Value One');
        $repository->upsert($donor->id, 'test_key', 'Test Value Two');

        $meta = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', 'test_key')
            ->getAll();

        $this->assertCount(1, $meta);
        $this->assertEquals('Test Value Two', $meta[0]->meta_value);
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertMetaShouldUpdateExistingMeta(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();

        DB::table('give_donormeta')
            ->insert([
                'donor_id' => $donor->id,
                'meta_key' => 'test_key',
                'meta_value' => 'Test Value One',
            ]);

        $repository->upsert($donor->id, 'test_key', 'Test Value Two');

        $meta = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', 'test_key')
            ->get()
            ->meta_value;

        $this->assertEquals('Test Value Two', $meta);
    }

}
