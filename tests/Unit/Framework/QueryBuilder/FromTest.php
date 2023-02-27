<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class FromTest extends TestCase
{

    public function testFrom()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from(DB::raw('posts'));

        $this->assertContains(
            'SELECT * FROM posts',
            $builder->getSQL()
        );
    }


    public function testFromAlias()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from(DB::raw('posts'), 'donations');

        $this->assertContains(
            'SELECT * FROM posts AS donations',
            $builder->getSQL()
        );
    }

    public function testMultipleFrom()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->from(DB::raw('postmeta'));

        $this->assertContains(
            'SELECT * FROM posts, postmeta',
            $builder->getSQL()
        );
    }

}
