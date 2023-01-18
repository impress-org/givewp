<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class JoinTest extends TestCase
{

    public function testLeftJoin()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('donationsTable.*', 'metaTable.*')
            ->from(DB::raw('posts'), 'donationsTable')
            ->leftJoin(DB::raw('give_donationmeta'), 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');

        $this->assertContains(
            'SELECT donationsTable.*, metaTable.* FROM posts AS donationsTable LEFT JOIN give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id',
            $builder->getSQL()
        );
    }


    public function testRightJoin()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('donationsTable.*', 'metaTable.*')
            ->from(DB::raw('posts'), 'donationsTable')
            ->rightJoin(DB::raw('give_donationmeta'), 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');

        $this->assertContains(
            'SELECT donationsTable.*, metaTable.* FROM posts AS donationsTable RIGHT JOIN give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id',
            $builder->getSQL()
        );
    }

    public function testInnerJoin()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('donationsTable.*', 'metaTable.*')
            ->from(DB::raw('posts'), 'donationsTable')
            ->innerJoin(DB::raw('give_donationmeta'), 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');

        $this->assertContains(
            'SELECT donationsTable.*, metaTable.* FROM posts AS donationsTable INNER JOIN give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id',
            $builder->getSQL()
        );
    }


    public function testJoin()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('donationsTable.*', 'metaTable.*')
            ->from(DB::raw('posts'), 'donationsTable')
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin(DB::raw('give_donationmeta'), 'metaTable')
                    ->on('donationsTable.ID', 'metaTable.donation_id');
            });

        $this->assertContains(
            'SELECT donationsTable.*, metaTable.* FROM posts AS donationsTable LEFT JOIN give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id',
            $builder->getSQL()
        );
    }


    public function testAdvancedJoin()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('donationsTable.*', 'metaTable.*')
            ->from(DB::raw('posts'), 'donationsTable')
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin(DB::raw('give_donationmeta'), 'metaTable')
                    ->on('donationsTable.ID', 'metaTable.donation_id')
                    ->andOn('metaTable.meta_key', '_give_donor_billing_first_name', true)
                    ->orOn('metaTable.meta_key', '_give_donor_billing_last_name', true);
            });

        $this->assertContains(
            "SELECT donationsTable.*, metaTable.* FROM posts AS donationsTable LEFT JOIN give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id AND metaTable.meta_key = '_give_donor_billing_first_name' OR metaTable.meta_key = '_give_donor_billing_last_name'",
            $builder->getSQL()
        );
    }


    public function testJoinRaw()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('ID')
            ->from(DB::raw('give_donations'))
            ->joinRaw('LEFT JOIN posts ON post_id = give_donations.id');

        $this->assertContains(
            "SELECT ID FROM give_donations LEFT JOIN posts ON post_id = give_donations.id",
            $builder->getSQL()
        );
    }

}
