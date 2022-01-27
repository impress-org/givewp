<?php

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\WhereQueryBuilder;
use PHPUnit\Framework\TestCase;

final class QueryBuilderTest extends TestCase
{

    public function testSelect()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('local_posts.ID')
            ->from('local_give_donations');

        $this->assertContains(
            'SELECT local_posts.ID FROM local_give_donations',
            $builder->getSQL()
        );
    }

    public function testSelectAlias()
    {
        $builder = new QueryBuilder();
        $builder
            ->select(['local_posts.ID', 'posts_id'])
            ->from('local_give_donations');


        $this->assertContains(
            'SELECT local_posts.ID AS posts_id FROM local_give_donations',
            $builder->getSQL()
        );
    }

    public function testFrom()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from('local_give_donations');

        $this->assertContains(
            'SELECT * FROM local_give_donations',
            $builder->getSQL()
        );
    }


    public function testFromAlias()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from('local_give_donations', 'donations');

        $this->assertContains(
            'SELECT * FROM local_give_donations AS donations',
            $builder->getSQL()
        );
    }


    public function testLeftJoin()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('donationsTable.*', 'metaTable.*')
            ->from('posts', 'donationsTable')
            ->leftJoin('give_donationmeta', 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');

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
            ->from('posts', 'donationsTable')
            ->rightJoin('give_donationmeta', 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');

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
            ->from('posts', 'donationsTable')
            ->innerJoin('give_donationmeta', 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');

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
            ->from('posts', 'donationsTable')
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_donationmeta', 'metaTable')
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
            ->from('posts', 'donationsTable')
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_donationmeta', 'metaTable')
                    ->on('donationsTable.ID', 'metaTable.donation_id')
                    ->and('metaTable.meta_key', '_give_donor_billing_first_name', true);
            });

        $this->assertContains(
            "SELECT donationsTable.*, metaTable.* FROM posts AS donationsTable LEFT JOIN give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id AND metaTable.meta_key = '_give_donor_billing_first_name'",
            $builder->getSQL()
        );
    }


    public function testWhere()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from('posts')
            ->where('ID', 5);

        $this->assertContains(
            "SELECT * FROM posts WHERE ID = '5'",
            $builder->getSQL()
        );
    }

    public function testAndWhere()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'published')
            ->where('post_title', 'Donation Form');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' AND post_title = 'Donation Form'",
            $builder->getSQL()
        );
    }

    public function testOrWhere()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'published')
            ->orWhere('post_title', 'Donation Form');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' OR post_title = 'Donation Form'",
            $builder->getSQL()
        );
    }

    public function testNestedWhere()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'subscription')
            ->where(function (WhereQueryBuilder $builder) {
                $builder
                    ->where('post_status', 'give_subscription')
                    ->orWhere('post_status', 'give_donation');
            });

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'subscription' AND ( post_status = 'give_subscription' OR post_status = 'give_donation')",
            $builder->getSQL()
        );
    }


    public function testSubSelectQuery()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'subscription')
            ->whereIn('ID', function (QueryBuilder $builder) {
                $builder
                    ->select(['meta_value', 'donation_id'])
                    ->from('give_donationmeta')
                    ->where('meta_key', 'donation_id');
            });

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'subscription' AND ID IN (SELECT meta_value AS donation_id FROM give_donationmeta WHERE meta_key = 'donation_id')",
            $builder->getSQL()
        );
    }

    public function testWhereIn()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'subscription')
            ->whereIn('ID', [1, 2, 3]);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'subscription' AND ID IN ('1','2','3')",
            $builder->getSQL()
        );
    }

    public function testWhereNotIn()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'subscription')
            ->whereNotIn('ID', [1, 2, 3]);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'subscription' AND ID NOT IN ('1','2','3')",
            $builder->getSQL()
        );
    }


    public function testOrWhereIn()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'subscription')
            ->orWhereIn('ID', [1, 2, 3]);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'subscription' OR ID IN ('1','2','3')",
            $builder->getSQL()
        );
    }

    public function testOrWhereNotIn()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'subscription')
            ->orWhereNotIn('ID', [1, 2, 3]);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'subscription' OR ID NOT IN ('1','2','3')",
            $builder->getSQL()
        );
    }


    public function testWhereBetween()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereBetween('ID', 0, 100);

        $this->assertContains(
            "SELECT * FROM posts WHERE ID BETWEEN '0' AND '100'",
            $builder->getSQL()
        );
    }


    public function testWhereBetweenDates()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereBetween('post_date', '2021-11-22', '2022-11-22');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_date BETWEEN '2021-11-22' AND '2022-11-22'",
            $builder->getSQL()
        );
    }


    public function testWhereNotBetween()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereNotBetween('ID', 0, 100);

        $this->assertContains(
            "SELECT * FROM posts WHERE ID NOT BETWEEN '0' AND '100'",
            $builder->getSQL()
        );
    }


    public function testOrWhereBetween()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('ID', 222)
            ->orWhereBetween('ID', 0, 100);

        $this->assertContains(
            "SELECT * FROM posts WHERE ID = '222' OR ID BETWEEN '0' AND '100'",
            $builder->getSQL()
        );
    }


    public function testOrWhereNotBetween()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('ID', 222)
            ->orWhereNotBetween('ID', 0, 100);

        $this->assertContains(
            "SELECT * FROM posts WHERE ID = '222' OR ID NOT BETWEEN '0' AND '100'",
            $builder->getSQL()
        );
    }


    public function testWhereLike()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereLike('post_title', 'Donation');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_title LIKE '%Donation%'",
            DB::remove_placeholder_escape($builder->getSQL())
        );
    }


    public function testWhereNotLike()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereNotLike('post_title', 'Donation');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_title NOT LIKE '%Donation%'",
            DB::remove_placeholder_escape($builder->getSQL())
        );
    }


    public function testOrWhereLike()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereLike('post_title', 'Form')
            ->orWhereLike('post_title', 'Donation');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_title LIKE '%Form%' OR post_title LIKE '%Donation%'",
            DB::remove_placeholder_escape($builder->getSQL())
        );
    }


    public function testOrWhereNotLike()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereLike('post_title', 'Form')
            ->orWhereNotLike('post_title', 'Donation');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_title LIKE '%Form%' OR post_title NOT LIKE '%Donation%'",
            DB::remove_placeholder_escape($builder->getSQL())
        );
    }


    public function testWhereIsNull()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereIsNull('post_parent');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent IS NULL",
            $builder->getSQL()
        );
    }


    public function testWhereIsNotNull()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->whereIsNotNull('post_parent');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent IS NOT NULL",
            $builder->getSQL()
        );
    }


    public function testOrWhereIsNull()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5, '>')
            ->orWhereIsNull('post_parent');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent > '5' OR post_parent IS NULL",
            $builder->getSQL()
        );
    }


    public function testOrWhereIsNotNull()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5, '>')
            ->orWhereIsNotNull('post_parent');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent > '5' OR post_parent IS NOT NULL",
            $builder->getSQL()
        );
    }


    public function testHavingCount()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingCount('ID', '>', 10);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING COUNT( ID) > '10'",
            $builder->getSQL()
        );
    }


    public function testHavingSum()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingSum('post_count', '>', 1000);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING SUM(post_count) > '1000'",
            $builder->getSQL()
        );
    }


    public function testHavingSumAnd()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingSum('post_count', '>', 1000)
            ->havingSum('post_count', '<', 5000);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING SUM(post_count) > '1000' AND SUM(post_count) < '5000'",
            $builder->getSQL()
        );
    }


    public function testOrHaving()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingSum('post_count', '>', 1000)
            ->orHavingSum('post_count', '<', 100);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING SUM(post_count) > '1000' OR SUM(post_count) < '100'",
            $builder->getSQL()
        );
    }


    public function testHavingMin()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingMin('post_count', '>', 1000);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING MIN(post_count) > '1000'",
            $builder->getSQL()
        );
    }


    public function testOrHavingMin()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingMin('post_count', '>', 1000)
            ->orHavingMin('post_count', '<', 100); // Doesn't make sense, but hey...

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING MIN(post_count) > '1000' OR MIN(post_count) < '100'",
            $builder->getSQL()
        );
    }


    public function testHavingMax()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingMax('post_count', '>', 1000);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING MAX(post_count) > '1000'",
            $builder->getSQL()
        );
    }


    public function testOrHavingMax()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingMax('post_count', '>', 1000)
            ->orHavingMax('post_count', '<', 100);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING MAX(post_count) > '1000' OR MAX(post_count) < '100'",
            $builder->getSQL()
        );
    }


    public function testHavingAvg()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingAvg('post_count', '>', 1000);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING AVG(post_count) > '1000'",
            $builder->getSQL()
        );
    }


    public function testOrHavingAvg()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingAvg('post_count', '<', 1000)
            ->orHavingAvg('post_count', '>', 10000);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING AVG(post_count) < '1000' OR AVG(post_count) > '10000'",
            $builder->getSQL()
        );
    }


    public function testOrderByDesc()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'published')
            ->orderBy('ID', 'DESC');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' ORDER BY ID DESC",
            $builder->getSQL()
        );
    }


    public function testOrderByAsc()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'published')
            ->orderBy('ID', 'ASC');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' ORDER BY ID ASC",
            $builder->getSQL()
        );
    }


    public function testGroupBy()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'published')
            ->groupBy('ID');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' GROUP BY ID",
            $builder->getSQL()
        );
    }


    public function testLimit()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'published')
            ->limit(5);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' LIMIT 5",
            $builder->getSQL()
        );
    }


    public function testOffset()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from('posts')
            ->where('post_status', 'published')
            ->limit(5)
            ->offset(10);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' LIMIT 5 OFFSET 10",
            $builder->getSQL()
        );
    }


    public function testAttachMeta()
    {
        $builder = new QueryBuilder();

        $builder
            ->from('wp_posts', 'posts')
            ->select(
                ['posts.ID', 'id'],
                ['posts.post_date', 'createdAt']
            )
            ->attachMeta(
                'wp_give_donationmeta',
                'posts.ID',
                'donation_id',
                ['_give_payment_total', 'amount']
            )
            ->leftJoin('wp_give_donationmeta', 'posts.ID', 'donationMeta.donation_id', 'donationMeta')
            ->where('posts.post_type', 'give_payment')
            ->where('posts.post_status', 'give_subscription')
            ->where('donationMeta.meta_key', 'subscription_id')
            ->where('donationMeta.meta_value', 1)
            ->orderBy('posts.post_date', 'DESC');

        $this->assertContains(
            "SELECT posts.ID AS id, posts.post_date AS createdAt, wp_give_donationmeta_attach_meta_0.meta_value AS amount FROM wp_posts AS posts LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_0 ON posts.ID = wp_give_donationmeta_attach_meta_0.donation_id AND wp_give_donationmeta_attach_meta_0.meta_key = '_give_payment_total' LEFT JOIN wp_give_donationmeta donationMeta ON posts.ID = donationMeta.donation_id WHERE posts.post_type = 'give_payment' AND posts.post_status = 'give_subscription' AND donationMeta.meta_key = 'subscription_id' AND donationMeta.meta_value = '1' ORDER BY posts.post_date DESC",
            $builder->getSQL()
        );
    }
}
