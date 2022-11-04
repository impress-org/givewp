<?php

namespace TestsNextGen\Unit\VieModels;

use Exception;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use Give\NextGen\DonationForm\Models\DonationForm;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

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

        $this->assertSame(
            $viewModel->storageData($formId),
            [
                'resourceURL' => rest_url(FormBuilderRestRouteConfig::NAMESPACE . '/form/' . $formId),
                'previewURL' => site_url("?givewp-view=donation-form&form-id=$formId"),
                'nonce' => wp_create_nonce('wp_rest'),
                'blockData' => get_post($formId)->post_content,
                'settings' => get_post_meta($formId, 'formBuilderSettings', true),
                'currency' => give_get_currency(),
            ]
        );
    }
}
