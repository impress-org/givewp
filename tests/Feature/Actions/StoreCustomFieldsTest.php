<?php

namespace Give\Tests\Feature\Actions;

use Give\DonationForms\Listeners\StoreCustomFields;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\FormAPI\Form\Text;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\ValueObjects\PersistenceScope;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class StoreCustomFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldStoreAsDonorMeta(): void
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

        $action($form, $donation, null, ['custom_text_block_meta' => 'Custom Text Block Value']);

        $query = DB::table('give_donormeta')
            ->select('meta_value')
            ->where('donor_id', $donation->donorId)
            ->where('meta_key', 'custom_text_block_meta')
            ->get();

        $this->assertSame('Custom Text Block Value', $query->meta_value);
    }

    /**
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldStoreAsDonationMeta(): void
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

        $action($form, $donation,null, ['custom_text_block_meta' => 'Custom Text Block Value']);

        $query = DB::table('give_donationmeta')
            ->select('meta_value')
            ->where('donation_id', $donation->id)
            ->where('meta_key', 'custom_text_block_meta')
            ->get();

        $this->assertSame('Custom Text Block Value', $query->meta_value);
    }

    /**
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    public function testShouldStoreAsSubscriptionMeta(): void
    {
        $field = \Give\Framework\FieldsAPI\Text::make('custom_subscription_field')
            ->metaKey('custom_subscription_field_meta')
            ->showInAdmin()
            ->defaultValue('Custom Subscription Field Value')
            ->scope(PersistenceScope::SUBSCRIPTION);

         add_action('givewp_donation_form_schema', static function (\Give\Framework\FieldsAPI\DonationForm $form) use ($field) {
            $form->insertAfter('email', $field);
        });

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation(['donationFormId' => $form->id]);

        $action = new StoreCustomFields();

        $action($form, $subscription->initialDonation(), $subscription, [$field->getName() => $field->getDefaultValue()]);

        $query = DB::table('give_subscriptionmeta')
            ->select('meta_value')
            ->where('subscription_id', $subscription->id)
            ->where('meta_key', $field->getMetaKey())
            ->get();

        $this->assertSame($field->getDefaultValue(), $query->meta_value);
    }
}
