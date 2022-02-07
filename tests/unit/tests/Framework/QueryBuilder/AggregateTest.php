<?php

namespace unit\tests\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 *
 * @covers Aggregate
 */
final class AggregateTest extends TestCase
{
    /**
     * Truncate posts and give_donationmeta table to avoid duplicate records
     *
     * @unreleased
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $posts        = DB::prefix('posts');
        $donationMeta = DB::prefix('give_donationmeta');

        DB::query("TRUNCATE TABLE $posts");
        DB::query("TRUNCATE TABLE $donationMeta");
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testCount()
    {
        $data = [
            [
                'post_title' => 'Query Builder Aggregate test 0',
            ],
            [
                'post_title' => 'Query Builder Aggregate test 1',
            ],
            [
                'post_title' => 'Query Builder Aggregate test 2',
            ]
        ];

        foreach ($data as $row) {
            DB::table('posts')->insert($row);
        }

        $count = DB::table('posts')->count();

        $this->assertEquals(3, $count);

        $count = DB::table('posts')
            ->where('post_type', 'dummy')
            ->count();

        $this->assertEquals(0, $count);
    }


    /**
     * @unreleased
     *
     * @return void
     */
    public function testCountByColumn()
    {
        $data = [
            [
                'donation_id' => 1,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 10,
            ],
            [
                'donation_id' => 2,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 20,
            ],
            [
                'donation_id' => 3,
                'meta_key'    => 'donation_amount',
                'meta_value'  => null,
            ]
        ];

        foreach ($data as $row) {
            DB::table('give_donationmeta')->insert($row);
        }

        $count = DB::table('give_donationmeta')->count('meta_key');

        $this->assertEquals(3, $count);

        $count = DB::table('give_donationmeta')->count('meta_value');

        $this->assertEquals(2, $count);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testSum()
    {
        $data = [
            [
                'donation_id' => 1,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 10,
            ],
            [
                'donation_id' => 2,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 20,
            ],
            [
                'donation_id' => 3,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 30,
            ]
        ];

        foreach ($data as $row) {
            DB::table('give_donationmeta')->insert($row);
        }

        $count = DB::table('give_donationmeta')
            ->where('meta_key', 'donation_amount')
            ->sum('meta_value');

        $this->assertEquals(60, $count);
    }


    /**
     * @unreleased
     *
     * @return void
     */
    public function testAvg()
    {
        $data = [
            [
                'donation_id' => 1,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 10,
            ],
            [
                'donation_id' => 2,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 20,
            ],
            [
                'donation_id' => 3,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 30,
            ]
        ];

        foreach ($data as $row) {
            DB::table('give_donationmeta')->insert($row);
        }

        $count = DB::table('give_donationmeta')
            ->where('meta_key', 'donation_amount')
            ->avg('meta_value');

        $this->assertEquals(20, $count);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testMin()
    {
        $data = [
            [
                'donation_id' => 1,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 10,
            ],
            [
                'donation_id' => 2,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 20,
            ],
            [
                'donation_id' => 3,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 30,
            ]
        ];

        foreach ($data as $row) {
            DB::table('give_donationmeta')->insert($row);
        }

        $count = DB::table('give_donationmeta')
            ->where('meta_key', 'donation_amount')
            ->min('meta_value');

        $this->assertEquals(10, $count);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testMax()
    {
        $data = [
            [
                'donation_id' => 1,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 10,
            ],
            [
                'donation_id' => 2,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 20,
            ],
            [
                'donation_id' => 3,
                'meta_key'    => 'donation_amount',
                'meta_value'  => 30,
            ]
        ];

        foreach ($data as $row) {
            DB::table('give_donationmeta')->insert($row);
        }

        $count = DB::table('give_donationmeta')
            ->where('meta_key', 'donation_amount')
            ->max('meta_value');

        $this->assertEquals(30, $count);
    }
}
