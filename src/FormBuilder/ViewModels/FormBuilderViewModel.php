<?php

namespace Give\FormBuilder\ViewModels;

use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\NextGen\Framework\FormDesigns\FormDesign;
use Give\NextGen\Framework\FormDesigns\Registrars\FormDesignRegistrar;

class FormBuilderViewModel
{
    /**
     * @unreleased
     */
    public function storageData(int $donationFormId): array
    {
        return [
            'resourceURL' => rest_url(FormBuilderRestRouteConfig::NAMESPACE . '/form/' . $donationFormId),
            'previewURL' => site_url("?givewp-view=donation-form&form-id=$donationFormId"),
            'nonce' => wp_create_nonce('wp_rest'),
            'blockData' => get_post($donationFormId)->post_content,
            'settings' => get_post_meta($donationFormId, 'formBuilderSettings', true),
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
