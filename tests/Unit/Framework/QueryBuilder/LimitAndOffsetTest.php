<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class LimitAndOffsetTest extends TestCase
{
    public function testLimit()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published')
            ->limit(5);

        $this->assertStringContainsString(
            "SELECT * FROM posts WHERE post_status = 'published' LIMIT 5",
            $builder->getSQL()
        );
    }


    public function testOffset()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published')
            ->limit(5)
            ->offset(10);

        $this->assertStringContainsString(
            "SELECT * FROM posts WHERE post_status = 'published' LIMIT 5 OFFSET 10",
            $builder->getSQL()
        );
    }

}
