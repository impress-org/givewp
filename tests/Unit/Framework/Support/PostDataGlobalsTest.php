<?php

namespace Give\Tests\Unit\Framework\Support;

use Faker\Factory;
use Faker\Generator;
use Give\Framework\Support\PostDataGlobals;
use Give\Tests\TestCase;

/**
 * @since TBD
 *
 * @covers \Give\Framework\Support\PostDataGlobals
 */
class PostDataGlobalsTest extends TestCase
{
    /**
     * @since TBD
     *
     * @var Generator
     */
    private $faker;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    /**
     * @since TBD
     */
    public function testRestoresTheValuesThatWereSetBeforeTheSnapshot(): void
    {
        $originalId = $this->faker->numberBetween(1, 1000);
        $originalPage = $this->faker->numberBetween(1, 1000);

        $GLOBALS['id'] = $originalId;
        $GLOBALS['page'] = $originalPage;

        $snapshot = PostDataGlobals::snapshot();

        $GLOBALS['id'] = $originalId + 1;
        $GLOBALS['page'] = $originalPage + 1;

        PostDataGlobals::restore($snapshot);

        $this->assertSame($originalId, $GLOBALS['id']);
        $this->assertSame($originalPage, $GLOBALS['page']);
    }

    /**
     * A global that did not exist beforehand must not exist afterwards either, otherwise restoring
     * declares it as null and code guarding with isset() starts taking the wrong branch.
     *
     * @since TBD
     */
    public function testUnsetsGlobalsThatDidNotExistBeforeTheSnapshot(): void
    {
        unset($GLOBALS['id']);

        $snapshot = PostDataGlobals::snapshot();

        $GLOBALS['id'] = $this->faker->numberBetween(1, 1000);

        PostDataGlobals::restore($snapshot);

        $this->assertArrayNotHasKey('id', $GLOBALS);
    }

    /**
     * @since TBD
     */
    public function testPreservesAGlobalThatWasExplicitlyNull(): void
    {
        $GLOBALS['authordata'] = null;

        $snapshot = PostDataGlobals::snapshot();

        $GLOBALS['authordata'] = $this->faker->word();

        PostDataGlobals::restore($snapshot);

        $this->assertArrayHasKey('authordata', $GLOBALS);
        $this->assertNull($GLOBALS['authordata']);
    }
}
