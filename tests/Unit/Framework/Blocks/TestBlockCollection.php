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
    protected $blocks;
    protected $blockCollection;

    /**
     * @unreleased
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->blocks = [
            new BlockModel('givewp/block1'),
            new BlockModel('givewp/block2'),
            new BlockModel('givewp/block3'),
        ];
        $this->blocks[1]->innerBlocks = BlockCollection::make([
            new BlockModel('givewp/block2-child1'),
            new BlockModel('givewp/block2-child2'),
        ]);

        $this->blockCollection = BlockCollection::make($this->blocks);
    }

    /**
     * @return void
     * @since 0.1.0
     *
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

    /**
     * @unreleased
     */
    public function testInsertBeforeAddsNewBlockBeforeReference()
    {
        $blockName = 'givewp/block2';
        $blockIndex = 0;
        $newBlock = new BlockModel('givewp/newBlock');

        $this->blockCollection->insertBefore($blockName, $newBlock, $blockIndex);

        $expectedBlocks = array_merge(
            array_slice($this->blocks, 0, 1),
            [$newBlock],
            array_slice($this->blocks, 1)
        );

        $this->assertEquals($expectedBlocks, $this->blockCollection->getBlocks());
    }

    /**
     * @unreleased
     */
    public function testInsertAfterAddsNewBlockAfterReference()
    {
        $blockName = 'givewp/block2';
        $blockIndex = 0;
        $newBlock = new BlockModel('givewp/newBlock');

        $this->blockCollection->insertAfter($blockName, $newBlock, $blockIndex);

        $expectedBlocks = array_merge(
            array_slice($this->blocks, 0, 2),
            [$newBlock],
            array_slice($this->blocks, 2)
        );

        $this->assertEquals($expectedBlocks, $this->blockCollection->getBlocks());
    }

    /**
     * @unreleased
     */
    public function testPrependAddsNewBlockAsFirstChild()
    {
        $blockName = 'givewp/block2';
        $blockIndex = 0;
        $newBlock = new BlockModel('givewp/newBlock');

        $this->blockCollection->prepend($blockName, $newBlock, $blockIndex);

        $expectedBlocks = $this->blocks;
        $expectedBlocks[1]->innerBlocks = BlockCollection::make(
            array_merge(
                [$newBlock],
                array_slice($this->blocks[1]->innerBlocks->toArray(), 1)
            )
        );

        $this->assertEquals($expectedBlocks, $this->blockCollection->getBlocks());
    }

    /**
     * @unreleased
     */
    public function testAppendAddsNewBlockAsLastChild()
    {
        $blockName = 'givewp/block2';
        $blockIndex = 0;
        $newBlock = new BlockModel('givewp/newBlock');

        $this->blockCollection->append($blockName, $newBlock, $blockIndex);

        $expectedBlocks = $this->blocks;
        $expectedBlocks[1]->innerBlocks = BlockCollection::make(
            array_merge(
                $this->blocks[1]->innerBlocks->toArray(),
                [$newBlock]
            )
        );

        $this->assertEquals($expectedBlocks, $this->blockCollection->getBlocks());
    }
}
