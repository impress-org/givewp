<?php

namespace Give\Tests\Unit\Framework\Blocks;

use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class TestBlockCollection extends TestCase
{
    protected $blocks;
    protected $blockCollection;

    /**
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
     *
     * @return void
     */
    public function testMakesCollectionFromJson()
    {
        $collection = BlockCollection::fromJson('[{"name":"namespace/nested-block"}]');

        $this->assertInstanceOf(BlockModel::class, $collection->getBlocks()[0]);
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
     */
    public function testPrependAddsNewBlockAsFirstChild()
    {
        $collection = BlockCollection::make([
            new BlockModel('namespace/nested-block', 'namespace/nested-block', true)
        ])
        ->prepend(
            $newBlock = new BlockModel('namespace/new-block', 'namespace/new-block', true)
        );

        $this->assertEquals($newBlock, $collection->getBlocks()[0]);
    }

    /**
     * @since 3.0.0
     */
    public function testAppendAddsNewBlockAsLastChild()
    {
        $collection = BlockCollection::make([
            new BlockModel('namespace/nested-block', 'namespace/nested-block', true)
        ])
        ->append(
            $newBlock = new BlockModel('namespace/new-block', 'namespace/new-block', true)
        );

        $this->assertEquals($newBlock, $collection->getBlocks()[1]);
    }
}
