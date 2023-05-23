<?php

namespace Give\Tests\Feature\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\NextGen\DonationForm\Listeners\StoreCustomFields;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class StoreCustomFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 0.1.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldStoreAsDonorMeta()
    {
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
                        'title' => 'Custom Text Field',
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
        $donation = Donation::factory()->create(['formId' => $form->id]);

        $action = new StoreCustomFields();

        $action($form, $donation, ['custom_text_block_meta' => 'Custom Text Block Value']);

        $query = DB::table('give_donormeta')
            ->select('meta_value')
            ->where('donor_id', $donation->donorId)
            ->where('meta_key', 'custom_text_block_meta')
            ->get();

        $this->assertSame('Custom Text Block Value', $query->meta_value);
    }

    /**
     * @since 0.1.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldStoreAsDonationMeta()
    {
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
                        'storeAsDonorMeta' => false,
                        'title' => 'Custom Text Field',
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
        $donation = Donation::factory()->create(['formId' => $form->id]);

        $action = new StoreCustomFields();

        $action($form, $donation, ['custom_text_block_meta' => 'Custom Text Block Value']);

        $query = DB::table('give_donationmeta')
            ->select('meta_value')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'custom_text_block_meta')
            ->get();

        $this->assertSame('Custom Text Block Value', $query->meta_value);
    }
}
