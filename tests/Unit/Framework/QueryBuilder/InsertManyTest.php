<?php
namespace Give\Tests\Unit\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 */
final class InsertManyTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testInsertManyQuery()
    {
        $testData = [
            [
                'post_title' => 'Query Builder CRUD test 1',
                'post_type' => 'crud_test',
                'post_content' => 'Hello World 1!',
            ],
            [
                'post_title' => 'Query Builder CRUD test 2',
                'post_type' => 'crud_test',
                'post_content' => 'Hello World 2!',
            ]
        ];

        $QB = DB::table('posts');

        foreach($testData as $data) {
            $QB->set($data);
        }

        $this->assertContains(
            "INSERT INTO '" . DB::prefix('posts') . "' (post_title, post_type, post_content) VALUES ('Query Builder CRUD test 1', 'crud_test', 'Hello World 1!'),('Query Builder CRUD test 2', 'crud_test', 'Hello World 2!')",
            $QB->getSQL()
        );
    }
}
