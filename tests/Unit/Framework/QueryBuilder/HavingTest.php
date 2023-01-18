<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class HavingTest extends TestCase
{

    public function testHaving()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_parent', 5)
            ->groupBy('id')
            ->having('id', '>', 10);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY id HAVING id > '10'",
            $builder->getSQL()
        );
    }

    public function testHavingCount()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
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
            ->from(DB::raw('posts'))
            ->where('post_parent', 5)
            ->groupBy('ID')
            ->havingAvg('post_count', '<', 1000)
            ->orHavingAvg('post_count', '>', 10000);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_parent = '5' GROUP BY ID HAVING AVG(post_count) < '1000' OR AVG(post_count) > '10000'",
            $builder->getSQL()
        );
    }

    public function testHavingRaw()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('ID')
            ->from(DB::raw('give_donations'))
            ->groupBy('id')
            ->havingRaw('HAVING COUNT(id) > %d', 1000);

        $this->assertContains(
            "SELECT ID FROM give_donations GROUP BY id HAVING COUNT(id) > 1000",
            $builder->getSQL()
        );
    }


    public function testHavingRawChain()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('ID')
            ->from(DB::raw('give_donations'))
            ->groupBy('id')
            ->havingRaw('HAVING COUNT(id) > %d', 1000)
            ->orHavingAvg('id', '<', 400);

        $this->assertContains(
            "SELECT ID FROM give_donations GROUP BY id HAVING COUNT(id) > 1000 OR AVG( id) < '400'",
            $builder->getSQL()
        );
    }


    public function testReturnExceptionWhenBadComparisonArgumentIsUsed() {
        $this->expectException( InvalidArgumentException::class );

        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->groupBy('ID')
            ->having('id', 'EQUALS TO', 10);
    }
}
