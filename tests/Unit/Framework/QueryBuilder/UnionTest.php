<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class UnionTest extends TestCase
{
    public function testUnion()
    {
        $builder1 = new QueryBuilder();
        $builder2 = new QueryBuilder();

        $builder1
            ->select('ID')
            ->from(DB::raw('give_donations'));

        $builder2
            ->select('ID')
            ->from(DB::raw('give_subscriptions'))
            ->where('ID', 100, '>')
            ->union($builder1);

        $this->assertContains(
            "SELECT ID FROM give_subscriptions WHERE ID > '100' UNION SELECT ID FROM give_donations",
            $builder2->getSQL()
        );
    }


    public function testUnionAll()
    {
        $builder1 = new QueryBuilder();
        $builder2 = new QueryBuilder();
        $builder3 = new QueryBuilder();

        $builder1
            ->select('ID')
            ->from(DB::raw('give_donations'));

        $builder2
            ->select('ID')
            ->from(DB::raw('give_subscriptions'))
            ->where('ID', 100, '>');

        $builder3
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published')
            ->unionAll($builder1, $builder2);

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' UNION ALL SELECT ID FROM give_donations UNION ALL SELECT ID FROM give_subscriptions WHERE ID > '100'",
            $builder3->getSQL()
        );
    }
}
