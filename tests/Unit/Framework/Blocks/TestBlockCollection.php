<?php

namespace Give\Tests\Unit\Framework\Blocks;

use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;

/**
 * @since 0.1.0
 */
class TestBlockCollection extends TestCase
{
    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function testMakesCollectionFromArray()
    {
        $blockModel = new BlockModel('namespace/nested-block', 'namespace/nested-block', true);
        $collection = BlockCollection::make([
            $blockModel
        ]);

        $this->assertInstanceOf(BlockModel::class, $collection->getBlocks()[0]);
    }

    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function testMakesCollectionFromJson()
    {
        $collection = BlockCollection::fromJson('[{"name":"namespace/nested-block"}]');

        $this->assertInstanceOf(BlockModel::class, $collection->getBlocks()[0]);
    }

    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function testCollectionReturnsArray()
    {
        $collection = BlockCollection::fromJson('[{"name":"namespace/nested-block", "clientId":"client-id"}]');

        $blockModel = new BlockModel('namespace/nested-block', 'client-id', true);

        $this->assertSame(
            [
                $blockModel->toArray()
            ],
            $collection->toArray()
        );
    }
}
