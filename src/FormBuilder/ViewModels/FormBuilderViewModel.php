<?php

namespace Give\FormBuilder\ViewModels;

use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\NextGen\DonationForm\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\Framework\FormDesigns\FormDesign;
use Give\NextGen\Framework\FormDesigns\Registrars\FormDesignRegistrar;

class FormBuilderViewModel
{
    /**
     * @unreleased
     */
    public function storageData(int $donationFormId): array
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::find($donationFormId);

        return [
            'resourceURL' => rest_url(FormBuilderRestRouteConfig::NAMESPACE . '/form/' . $donationFormId),
            'previewURL' => (new GenerateDonationFormPreviewRouteUrl())($donationFormId),
            'nonce' => wp_create_nonce('wp_rest'),
            'blockData' => $donationForm->blocks->toJson(),
            'settings' => $donationForm->settings->toJson(),
            'currency' => give_get_currency(),
            'formDesigns' => array_map(static function ($designClass) {
                /** @var FormDesign $design */
                $design = give($designClass);

                return [
                    'id' => $design::id(),
                    'name' => $design::name(),
                ];
            }, give(FormDesignRegistrar::class)->getDesigns()),
        ];
    }

    /**
     * @unreleased
     */
    public function jsPathFromRoot(): string
    {
        return 'packages/form-builder/build/givewp-form-builder.js';
    }
}
