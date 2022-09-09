<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Give\NextGen\DonationForm\Blocks\DonationFormBlock\DataTransferObjects\BlockAttributes;
use GiveTests\TestCase;

/**
 * @unreleased
 */
class BlockAttributesTest extends TestCase
{
      /**
     * @unreleased
     *
     * @return array[]
     */
     public function attributesProvider(): array
     {
        return array(
          ['formId' => 1, 'formTemplateId' => 1],
          ['formId' => null, 'formTemplateId' => null],
          ['formId' => null, 'formTemplateId' => 1],
          ['formId' => 1, 'formTemplateId' => null],
          ['formId' => 1, null],
          [null, 'formTemplateId' => 1],
          [null, null],
        );
    }

    /**
     * @unreleased
     *
     * @dataProvider attributesProvider
     *
     * @return void
     */
    public function testShouldReturnAttributesArray($formId, $formTemplateId)
    {
        $blockAttributes = BlockAttributes::fromArray([
            'formId' => $formId,
            'formTemplateId' => $formTemplateId
        ]);

        $this->assertSame(['formId' => $formId, 'formTemplateId' => $formTemplateId], $blockAttributes->toArray());
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
