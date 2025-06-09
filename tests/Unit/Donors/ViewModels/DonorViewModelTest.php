<?php

namespace Give\Tests\Unit\Donors\ViewModels;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Donors\ViewModels\DonorViewModel;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class DonorViewModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testExportsShouldIncludeCustomFieldsKey()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $viewModel = new DonorViewModel($donor);
        $exports = $viewModel->exports();

        // Debug: let's see what keys are actually in the exports
        $this->assertTrue(array_key_exists('customFields', $exports), 'customFields key should exist in exports. Available keys: ' . implode(', ', array_keys($exports)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testExportsShouldIncludeCustomFieldsWhenSensitiveDataIncluded()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        // Create a custom field block
        $customFieldBlockModel = BlockModel::make([
            'name' => 'givewp/section',
            'attributes' => ['title' => '', 'description' => ''],
            'innerBlocks' => [
                [
                    'name' => 'givewp/text',
                    'attributes' => [
                        'fieldName' => 'custom_text_field',
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

        // Add custom field meta
        give()->donor_meta->add_meta($donor->id, 'custom_text_field', 'Custom Field Value');

        $viewModel = new DonorViewModel($donor);
        $viewModel->includeSensitiveData(true); // Include sensitive data
        $exports = $viewModel->exports();

        $this->assertTrue(isset($exports['customFields']));
        $this->assertTrue(is_array($exports['customFields']));
        $this->assertEquals(1, count($exports['customFields']));
        $this->assertEquals('Custom Text Field', $exports['customFields'][0]['label']);
        $this->assertEquals('Custom Field Value', $exports['customFields'][0]['value']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testExportsShouldReturnEmptyCustomFieldsWhenSensitiveDataExcluded()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        // Create a custom field block
        $customFieldBlockModel = BlockModel::make([
            'name' => 'givewp/section',
            'attributes' => ['title' => '', 'description' => ''],
            'innerBlocks' => [
                [
                    'name' => 'givewp/text',
                    'attributes' => [
                        'fieldName' => 'custom_text_field',
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

        // Add custom field meta
        give()->donor_meta->add_meta($donor->id, 'custom_text_field', 'Sensitive Custom Field Value');

        $viewModel = new DonorViewModel($donor);
        // Don't include sensitive data (default behavior)
        $exports = $viewModel->exports();

        $this->assertTrue(isset($exports['customFields']));
        $this->assertTrue(is_array($exports['customFields']));
        $this->assertTrue(empty($exports['customFields'])); // Should be empty when sensitive data is excluded
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testExportsShouldReturnEmptyCustomFieldsWhenNoneExist()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $viewModel = new DonorViewModel($donor);
        $viewModel->includeSensitiveData(true); // Include sensitive data
        $exports = $viewModel->exports();

        $this->assertTrue(isset($exports['customFields']));
        $this->assertTrue(is_array($exports['customFields']));
        $this->assertTrue(empty($exports['customFields']));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testExportsShouldFilterOutEmptyCustomFields()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        // Create a custom field block
        $customFieldBlockModel = BlockModel::make([
            'name' => 'givewp/section',
            'attributes' => ['title' => '', 'description' => ''],
            'innerBlocks' => [
                [
                    'name' => 'givewp/text',
                    'attributes' => [
                        'fieldName' => 'custom_text_field',
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

        // Add empty custom field meta
        give()->donor_meta->add_meta($donor->id, 'custom_text_field', '');

        $viewModel = new DonorViewModel($donor);
        $viewModel->includeSensitiveData(true); // Include sensitive data
        $exports = $viewModel->exports();

        $this->assertTrue(isset($exports['customFields']));
        $this->assertTrue(is_array($exports['customFields']));
        $this->assertTrue(empty($exports['customFields']));
    }
}
