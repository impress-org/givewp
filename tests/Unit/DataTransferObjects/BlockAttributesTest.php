<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Give\DonationForms\Blocks\DonationFormBlock\DataTransferObjects\BlockAttributes;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class BlockAttributesTest extends TestCase
{

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function testShouldReturnAttributesArray()
    {
        $attributes = [
            'formId' => 1,
            'blockId' => '123',
            'formFormat' => 'modal',
            'openFormButton' => 'Open Form'
        ];

        $blockAttributes = BlockAttributes::fromArray($attributes);

        $this->assertSame($attributes, $blockAttributes->toArray());
    }

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function testFormIdShouldReturnIntFromString()
    {
        $blockAttributes = BlockAttributes::fromArray([
            'formId' => '1',
            'blockId' => '123'
        ]);

        $this->assertSame(1, $blockAttributes->formId);
    }

}
