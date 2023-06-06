<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Exception;
use Give\DonationForms\Actions\ConvertDonationFormBlocksToFieldsApi;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\Email;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

final class TestConvertDonationFormBlocksToFieldsApi extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnFormSchema()
    {
        $block = BlockModel::make([
            'clientId' => '8371d4c7-0e8d-4aff-a1a1-b4520f008132',
            'name' => 'givewp/section',
            'isValid' => true,
            'attributes' => [
                'title' => 'custom section title',
                'description' => 'custom section description',
            ],
            'innerBlocks' => [
                [
                    'clientId' => 'bddaa0ea-29bf-4143-b62d-aae3396e9b0f',
                    'name' => 'givewp/text',
                    'isValid' => true,
                    'attributes' => [
                        'fieldName' => 'givewp-custom-field-name',
                        'label' => 'GiveWP Custom Block',
                    ],
                ],
            ],
        ]);

        $blockIndex = 1;
        $formId = 1;

        $blocks = BlockCollection::make([$block]);

        $formSchema = (new ConvertDonationFormBlocksToFieldsApi())($blocks, $formId);

        $form = new Form('donation-form');
        $form->append(
            Section::make('section-' . $blockIndex)
                ->label('custom section title')
                ->description('custom section description')
                ->append(
                    Text::make('givewp-custom-field-name')
                        ->label('GiveWP Custom Block')
                        ->storeAsDonorMeta(false)
                )
        );

        $this->assertEquals($formSchema, $form);
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnFormSchemaUsingFilter()
    {
        $section = BlockModel::make([
            'clientId' => '8371d4c7-0e8d-4aff-a1a1-b4520f008132',
            'name' => 'givewp/section',
            'isValid' => true,
            'attributes' => [
                'title' => 'custom section title',
                'description' => 'custom section description',
            ],
            'innerBlocks' => [
                [
                    'clientId' => 'bddaa0ea-29bf-4143-b62d-aae3396e9b0f',
                    'name' => 'givewp/givewp-custom-block',
                    'isValid' => true,
                    'attributes' => [
                        'label' => 'GiveWP Custom Block'
                    ],
                ],
            ],
        ]);

        $blockIndex = 1;
        $formId = 1;

        $blocks = BlockCollection::make([$section]);

        $block = $section->innerBlocks->getBlocks()[0];

        $customField = Email::make('givewp-custom-block')
            ->label($block->getAttribute('label'));

        add_filter(
            'givewp_donation_form_block_render_givewp/givewp-custom-block',
            static function (BlockModel $block, int $blockIndex) {
                return Email::make('givewp-custom-block');
            },
            10,
            2
        );

        $formSchema = (new ConvertDonationFormBlocksToFieldsApi())($blocks, $formId);

        $form = new Form('donation-form');
        $form->append(
            Section::make('section-' . $blockIndex)
                ->label('custom section title')
                ->description('custom section description')
                ->append($customField)
        );

        $this->assertEquals($formSchema, $form);
    }
}
