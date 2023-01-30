<?php

namespace Give\Tests\Unit\Framework\Blocks;

use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;

/**
 * @since 0.1.0
 */
class TestBlockModel extends TestCase
{
    /**
     * @since 0.1.0
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
     * @since 0.1.0
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
     * @since 0.1.0
     * @return void
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
     * @since 0.1.0
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
