<?php

namespace TestsNextGen\Unit\VieModels;

use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;
use TestsNextGen\TestTraits\HasMockForm;

class FormBuilderViewModelTest extends TestCase
{
    use RefreshDatabase;
    use HasMockForm;

    /**
     * @unreleased 
     *
     * @return void
     */
    public function testShouldReturnStorageData()
    {
        $viewModel = new FormBuilderViewModel();
        $mockForm = $this->createMockForm();
        $formId = $mockForm->id;

        $this->assertSame(
            $viewModel->storageData($formId),
            [
                'resourceURL' => rest_url(FormBuilderRestRouteConfig::NAMESPACE . '/form/' . $formId),
                'nonce' => wp_create_nonce('wp_rest'),
                'blockData' => get_post($formId)->post_content,
                'settings' => get_post_meta($formId, 'formBuilderSettings', true),
                'currency' => give_get_currency(),
            ]
        );
    }
}
