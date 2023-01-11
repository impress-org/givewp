<?php

namespace Give\Tests\Unit\VieModels;

use Exception;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use Give\NextGen\DonationForm\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class FormBuilderViewModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnStorageData()
    {
        $viewModel = new FormBuilderViewModel();
        /** @var DonationForm $mockForm */
        $mockForm = DonationForm::factory()->create();
        $formId = $mockForm->id;

        $this->assertArraySubset(
            [
                'resourceURL' => rest_url(FormBuilderRestRouteConfig::NAMESPACE . '/form/' . $formId),
                'previewURL' => (new GenerateDonationFormPreviewRouteUrl())($formId),
                'nonce' => wp_create_nonce('wp_rest'),
                'blockData' => get_post_meta($formId, 'formBuilderFields', true),
                'settings' => get_post_meta($formId, 'formBuilderSettings', true),
                'currency' => give_get_currency(),
            ],
            $viewModel->storageData($formId)
        );
    }
}
