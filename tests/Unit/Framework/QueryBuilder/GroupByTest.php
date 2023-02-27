<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class GroupByTest extends TestCase
{
    public function testGroupBy()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published')
            ->groupBy('ID');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' GROUP BY ID",
            $builder->getSQL()
        );
    }
}
