<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Give\NextGen\DonationForm\Blocks\DonationFormBlock\DataTransferObjects\BlockAttributes;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class BlockAttributesTest extends TestCase
{

    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnAttributesArray()
    {
        $blockAttributes = BlockAttributes::fromArray([
            'formId' => 1
        ]);

        $this->assertSame(['formId' => 1], $blockAttributes->toArray());
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testFormIdShouldReturnIntFromString()
    {
      $blockAttributes = BlockAttributes::fromArray([
            'formId' => '1',
      ]);

        $this->assertSame(1, $blockAttributes->formId);
    }

}
