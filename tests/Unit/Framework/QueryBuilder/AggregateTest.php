<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use PHPUnit\Framework\TestCase;

/**
 * @since 2.19.0
 *
 * @covers Aggregate
 */
final class AggregateTest extends TestCase
{
    /**
     * Truncate posts and give_donationmeta table to avoid duplicate records
     *
     * @since 2.19.0
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
     *
     * @since 2.19.0
     *
     * @return void
     */
    public function testCountShouldReturnTheTotalNumberOfRecords()
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

        $postsCount = DB::table('posts')->count();

        $this->assertEquals(3, $postsCount);

        $nonExistentPostTypeCount = DB::table('posts')
            ->where('post_type', 'dummy')
            ->count();

        $this->assertEquals(0, $nonExistentPostTypeCount);
    }


    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testCountByColumnShouldReturnTheTotalNumberOfRecords()
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

        $postsCount = DB::table('give_donationmeta')->count('meta_key');

        $this->assertEquals(3, $postsCount);

        $postsCountWithNullColumn = DB::table('give_donationmeta')->count('meta_value');

        $this->assertEquals(2, $postsCountWithNullColumn);
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testSumShouldReturnTheSumOfColumns()
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

        $totalSum = DB::table('give_donationmeta')
            ->where('meta_key', 'donation_amount')
            ->sum('meta_value');

        $this->assertEquals(60, $totalSum);
    }


    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testAvgShouldReturnTheAvgColumnValue()
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

        $avgValue = DB::table('give_donationmeta')
            ->where('meta_key', 'donation_amount')
            ->avg('meta_value');

        $this->assertEquals(20, $avgValue);
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testMinShouldReturnTheMinimumColumnValue()
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

        $minValue = DB::table('give_donationmeta')
            ->where('meta_key', 'donation_amount')
            ->min('meta_value');

        $this->assertEquals(10, $minValue);
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testMaxShouldReturnTheMaximumColumnValue()
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

        $maxValue = DB::table('give_donationmeta')
            ->where('meta_key', 'donation_amount')
            ->max('meta_value');

        $this->assertEquals(30, $maxValue);
    }
}
