<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\WhereQueryBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WhereTest extends TestCase
{
    public function testWhere()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
            ->where('post_status', 'subscription')
            ->whereIn('ID', function (QueryBuilder $builder) {
                $builder
                    ->select(['meta_value', 'donation_id'])
                    ->from(DB::raw('give_donationmeta'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
            ->whereLike('post_title', 'Donation');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_title LIKE '%Donation%'",
            DB::remove_placeholder_escape($builder->getSQL())
        );
    }

    public function testWhereLikeWithWildCardPositionLeft()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->whereLike('post_title', '%Donation');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_title LIKE '%Donation'",
            DB::remove_placeholder_escape($builder->getSQL())
        );
    }


    public function testWhereLikeWithWildCardPositionRight()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->whereLike('post_title', 'Donation%');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_title LIKE 'Donation%'",
            DB::remove_placeholder_escape($builder->getSQL())
        );
    }


    public function testWhereNotLike()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
            ->where('post_parent', 5, '>')
            ->orWhereIsNotNull('post_parent');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent > '5' OR post_parent IS NOT NULL",
            $builder->getSQL()
        );
    }


    public function testWhereExists()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->whereExists(function (QueryBuilder $builder) {
                $builder
                    ->select(['meta_value', 'donation_id'])
                    ->from(DB::raw('give_donationmeta'))
                    ->where('meta_key', 'donation_id');
            });

        $this->assertContains(
            "SELECT * FROM posts WHERE EXISTS (SELECT meta_value AS donation_id FROM give_donationmeta WHERE meta_key = 'donation_id')",
            $builder->getSQL()
        );
    }


    public function testWhereNotExists()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->whereNotExists(function (QueryBuilder $builder) {
                $builder
                    ->select(['meta_value', 'donation_id'])
                    ->from(DB::raw('give_donationmeta'))
                    ->where('meta_key', 'donation_id');
            });

        $this->assertContains(
            "SELECT * FROM posts WHERE NOT EXISTS (SELECT meta_value AS donation_id FROM give_donationmeta WHERE meta_key = 'donation_id')",
            $builder->getSQL()
        );
    }


    public function testWhereRaw()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('ID')
            ->from(DB::raw('give_donations'))
            ->whereRaw('WHERE post_id = %d AND post_title = %s', 5, 'Donation');

        $this->assertContains(
            "SELECT ID FROM give_donations WHERE post_id = 5 AND post_title = 'Donation'",
            $builder->getSQL()
        );
    }


    public function testWhereRawChain()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('ID')
            ->from(DB::raw('give_donations'))
            ->whereRaw('WHERE post_id = %d AND post_title = %s', 5, 'Donation')
            ->orWhere('post_title', 'Form');

        $this->assertContains(
            "SELECT ID FROM give_donations WHERE post_id = 5 AND post_title = 'Donation' OR post_title = 'Form'",
            $builder->getSQL()
        );
    }


    public function testReturnExceptionWhenBadComparisonArgumentIsUsed() {
        $this->expectException( InvalidArgumentException::class );

        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published', 'EQUALS TO');
    }

}
