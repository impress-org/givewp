<?php

namespace Give\DonationForms\Migrations;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\Tests\TestCase;

class UpdateDonationLevelsSchemaTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testAmountLevelsAreMigratedToNewSchema()
    {
        // Prepare
       $blockCollection = (new GenerateDefaultDonationFormBlockCollection())();
       $blockCollection->findByName('givewp/donation-amount')
           ->setAttribute('levels', ['10', '25', '50', '100', '250', '500'])
           ->setAttribute('defaultLevel', '100');

        $form = DonationForm::factory()->create([
            'blocks' => $blockCollection,
        ]);

        // Act
        $migration = new UpdateDonationLevelsSchema();
        $migration->run();

        // Assert
        $form = DonationForm::find($form->id);
        $block = $form->blocks->findByName('givewp/donation-amount');

        $this->assertIsArray($block->getAttribute('levels'));
        $this->assertEquals([
            ['label' => '', 'value' => '10', 'checked' => false],
            ['label' => '', 'value' => '25', 'checked' => false],
            ['label' => '', 'value' => '50', 'checked' => false],
            ['label' => '', 'value' => '100', 'checked' => true],
            ['label' => '', 'value' => '250', 'checked' => false],
            ['label' => '', 'value' => '500', 'checked' => false],
        ], $block->getAttribute('levels'));
    }
}
