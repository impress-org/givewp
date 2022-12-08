<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class SelectTest extends TestCase
{

    public function testSelect()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('ID')
            ->from(DB::raw('posts'));

        $this->assertContains(
            'SELECT ID FROM posts',
            $builder->getSQL()
        );
    }

    public function testSelectAll()
    {
        $builder = new QueryBuilder();
        $builder->from(DB::raw('posts'));

        $this->assertContains(
            'SELECT * FROM posts',
            $builder->getSQL()
        );
    }


    public function testSelectAlias()
    {
        $builder = new QueryBuilder();
        $builder
            ->select(['ID', 'posts_id'])
            ->from(DB::raw('posts'));


        $this->assertContains(
            'SELECT ID AS posts_id FROM posts',
            $builder->getSQL()
        );
    }

    public function testSelectDistinct()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('ID', 'post_author')
            ->from(DB::raw('posts'))
            ->distinct();

        $this->assertContains(
            'SELECT DISTINCT ID, post_author FROM posts',
            $builder->getSQL()
        );
    }

    public function testSelectRaw()
    {
        $builder = new QueryBuilder();

        $builder->selectRaw('SELECT * FROM posts WHERE post_status = %s', 'give_subscription');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'give_subscription'",
            $builder->getSQL()
        );
    }

    public function testSelectRawChain()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('ID', 'post_title')
            ->selectRaw('(SELECT COUNT(ID) FROM posts WHERE post_status = %s) as post_count', 'give_subscription')
            ->from(DB::raw('posts'));

        $this->assertContains(
            "SELECT ID, post_title, (SELECT COUNT(ID) FROM posts WHERE post_status = 'give_subscription') as post_count FROM posts",
            $builder->getSQL()
        );
    }

}
