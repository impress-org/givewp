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
        $repository->upsert($donation->id, 'test_key_two', null);
        $repository->upsert($donation->id, 'test_key_two', 'Test Value Three');

        $meta1 = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'test_key')
            ->getAll();

        $meta2 = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'test_key_two')
            ->getAll();

        $this->assertCount(1, $meta1);
        $this->assertEquals('Test Value Two', $meta1[0]->meta_value);
        $this->assertCount(1, $meta2);
        $this->assertEquals('Test Value Three', $meta2[0]->meta_value);
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertShouldInsertNewMeta(): void
    {
        $donation = Donation::factory()->create();
        $repository = new DonationMetaRepository();
        $repository->upsert($donation->id, 'test_key_string', 'Test Value');
        $repository->upsert($donation->id, 'test_key_array', ['One', 'Two', 'Three']);
        $repository->upsert($donation->id, 'test_key_int', 1);

        $meta1 = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'test_key_string')
            ->get()
            ->meta_value;

        $meta2 = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'test_key_array')
            ->get()
            ->meta_value;

        $meta3 = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'test_key_int')
            ->get()
            ->meta_value;

        $this->assertEquals('Test Value', $meta1);
        $this->assertEquals(['One', 'Two', 'Three'], json_decode($meta2, false));
        $this->assertEquals(1, $meta3);
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

    /**
     * @unreleased
     * @throws Exception
     */
    public function testGetShouldReturnNullIfMetaDoesNotExist(): void
    {
        $donation = Donation::factory()->create();
        $repository = new DonationMetaRepository();

        $meta = $repository->get($donation->id, 'test_key');

        $this->assertNull($meta);
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testGetShouldReturnMetaValueIfExists(): void
    {
        $donation = Donation::factory()->create();
        $repository = new DonationMetaRepository();

        DB::table('give_donationmeta')
            ->insert([
                'donation_id' => $donation->id,
                'meta_key' => 'test_key',
                'meta_value' => 'Test Value',
            ]);

        $meta = $repository->get($donation->id, 'test_key');

        $this->assertEquals('Test Value', $meta);
    }

    /**
     * @unreleased
     */
    public function testExistsShouldReturnTrue(): void
    {
        $donation = Donation::factory()->create();
        $repository = new DonationMetaRepository();

        DB::table('give_donationmeta')
            ->insert([
                'donation_id' => $donation->id,
                'meta_key' => 'test_key',
                'meta_value' => 'Test Value',
            ]);

        DB::table('give_donationmeta')
            ->insert([
                'donation_id' => $donation->id,
                'meta_key' => 'test_key_two',
                'meta_value' => null,
            ]);

        $exists = $repository->exists($donation->id, 'test_key');
        $exists2 = $repository->exists($donation->id, 'test_key_two');

        $this->assertTrue($exists);
        $this->assertTrue($exists2);
    }

    /**
     * @unreleased
     */
    public function testExistsShouldReturnFalse(): void
    {
        $donation = Donation::factory()->create();
        $repository = new DonationMetaRepository();

        $exists = $repository->exists($donation->id, 'test_key');

        $this->assertFalse($exists);
    }
}
