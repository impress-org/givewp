<?php

namespace Give\Tests\Unit\Framework\Blocks;

use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class TestBlockCollection extends TestCase
{
    /**
     * @unreleased
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
     * @unreleased
     *
     * @return void
     */
    public function testMakesCollectionFromJson()
    {
        $collection = BlockCollection::fromJson('[{"name":"namespace/nested-block"}]');

        $this->assertInstanceOf(BlockModel::class, $collection->getBlocks()[0]);
    }

    /**
     * @unreleased
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
