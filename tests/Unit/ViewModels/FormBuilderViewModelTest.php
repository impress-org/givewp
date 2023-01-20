<?php

namespace Give\Tests\Unit\VieModels;

use Exception;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use Give\NextGen\DonationForm\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\Framework\FormDesigns\FormDesign;
use Give\NextGen\Framework\FormDesigns\Registrars\FormDesignRegistrar;
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

        $this->assertSame(
            [
                'resourceURL' => rest_url(FormBuilderRestRouteConfig::NAMESPACE . '/form/' . $formId),
                'previewURL' => (new GenerateDonationFormPreviewRouteUrl())($formId),
                'nonce' => wp_create_nonce('wp_rest'),
                'blockData' => $mockForm->blocks->toJson(),
                'settings' => $mockForm->settings->toJson(),
                'currency' => give_get_currency(),
                'formDesigns' => array_map(static function ($designClass) {
                    /** @var FormDesign $design */
                    $design = give($designClass);

                    return [
                        'id' => $design::id(),
                        'name' => $design::name(),
                    ];
                }, give(FormDesignRegistrar::class)->getDesigns()),
                'formPage' => [
                    'isEnabled' => give_is_setting_enabled(give_get_option('forms_singular')),
                    // Note: Boolean values must be nested in an array to maintain boolean type, see \WP_Scripts::localize().
                    'permalink' => add_query_arg(['p' => $formId], site_url()),
                    'rewriteSlug' => get_post_type_object('give_forms')->rewrite['slug'],
                ],
            ],
            $viewModel->storageData($formId)
        );
    }
}
