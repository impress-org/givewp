<?php

namespace Give\Tests\Unit\Framework\Blocks;

use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class TestBlockModel extends TestCase
{
    /**
     * @since 3.0.0
     * @return void
     */
    public function testHasName()
    {
        $block = BlockModel::make([
            'name' => 'namespace/block-name',
        ]);

        $this->assertEquals('namespace/block-name', $block->name);
        $this->assertEquals('block-name', $block->getShortName());
    }

    /**
     * @since 3.0.0
     * @return void
     */
    public function testHasAttributes()
    {
        $block = BlockModel::make([
            'name' => 'namespace/block-name',
            'attributes' => [
                'foo' => 'bar'
            ],
        ]);

        $this->assertTrue($block->hasAttribute('foo'));
        $this->assertEquals('bar', $block->getAttribute('foo'));
    }

    /**
     * @since 3.0.0
     * @return void
     */
    public function testSetAttributes()
    {
        $block = BlockModel::make([
            'name' => 'namespace/block-name',
            'attributes' => [
                'foo' => 'bar'
            ],
        ]);

        $block->setAttribute('foo', 'baz');
        $this->assertEquals('baz', $block->getAttribute('foo'));
    }

    /**
     * @return void
     * @since 3.0.0
     */
    public function testHasInnerBlocksCollection()
    {
        $block = BlockModel::make([
            'name' => 'namespace/block-name',
            'innerBlocks' => [
                [
                    'name' => 'namespace/nested-block'
                ]
            ]
        ]);

        $this->assertInstanceOf(BlockCollection::class, $block->innerBlocks);
    }

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function testBlockModelReturnsArray()
    {
        $blockModel = new BlockModel(
            'namespace/block',
            'client-id',
            true,
            ['title' => 'My Block'],
            new BlockCollection([
                new BlockModel('namespace/inner-block', 'client-id', true, ['title' => 'My Inner Block'])
            ])
        );

        $this->assertSame(
            [
                'name' => 'namespace/block',
                'clientId' => 'client-id',
                'isValid' => true,
                'attributes' => [
                    'title' => 'My Block'
                ],
                'innerBlocks' => [
                    [
                        'name' => 'namespace/inner-block',
                        'clientId' => 'client-id',
                        'isValid' => true,
                        'attributes' => [
                            'title' => 'My Inner Block'
                        ],
                        'innerBlocks' => [
                        ]
                    ]
                ]
            ],
            $blockModel->toArray()
        );
    }
}
