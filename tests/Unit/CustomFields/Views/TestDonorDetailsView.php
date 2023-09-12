<?php

namespace Give\Tests\Unit\CustomFields\Views;

use Give\DonationForms\Listeners\StoreCustomFields;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donors\CustomFields\Views\DonorDetailsView;
use Give\Donors\Models\Donor;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonorDetailsView extends TestCase
{
    use RefreshDatabase;

    public function testRenderShouldReturnCustomFieldsHtml()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $customFieldBlockModel = BlockModel::make([
            'name' => 'givewp/section',
            'attributes' => ['title' => '', 'description' => ''],
            'innerBlocks' => [
                [
                    'name' => 'givewp/text',
                    'attributes' => [
                        'fieldName' => 'custom_text_block_meta',
                        'storeAsDonorMeta' => true,
                        'storeAsDonationMeta' => false,
                        'displayInAdmin' => true,
                        'title' => 'Custom Text Field',
                        'label' => 'Custom Text Field',
                        'description' => ''
                    ],
                ]
            ]
        ]);

        $form->blocks = BlockCollection::make(
            array_merge([$customFieldBlockModel], $form->blocks->getBlocks())
        );

        $form->save();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['formId' => $form->id, 'donorId' => $donor->id]);

        (new StoreCustomFields())($form, $donation, null, ['custom_text_block_meta' => 'Custom Text Block Value']);

        $form = DonationForm::find($form->id);

        $fields = array_filter($form->schema()->getFields(), static function ($field) {
            return $field->shouldShowInAdmin() && $field->shouldStoreAsDonorMeta();
        });

        $donorDetailsView = new DonorDetailsView($donation->donor, $fields);

        $mockRender = "<h3>Custom Fields</h3>
        <table class='wp-list-table widefat striped donations'>
			<thead>
                <tr>
                    <th scope='col'>Field</th>
                    <th scope='col'>Value</th>
                </tr>
			</thead>
			<tbody>
			    <tr>
                    <td>Custom Text Field</td>
                    <td>Custom Text Block Value</td>
                </tr>
            </tbody>
		</table>";

        $this->assertSame(
            preg_replace(
                "/\s+/",
                "",
                $mockRender
            ),
            preg_replace(
                "/\s+/",
                "",
                $donorDetailsView->render()
            )
        );
    }

}
