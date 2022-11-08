<?php

namespace Give\FormBuilder\ViewModels;

use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\NextGen\Framework\FormTemplates\FormTemplate;
use Give\NextGen\Framework\FormTemplates\Registrars\FormTemplateRegistrar;

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
            'templates' => array_map(static function ($templateClass) {
                /** @var FormTemplate $template */
                $template = give($templateClass);
                
                return [
                    'id' => $template::id(),
                    'name' => $template::name(),
                ];
            }, give(FormTemplateRegistrar::class)->getTemplates()),
        ];
    }

    /**
     * @unreleased
     */
    public function shadowDomStyles(): string
    {
        return file_get_contents(trailingslashit(GIVE_NEXT_GEN_DIR) . 'packages/form-builder/build/' . $this->css());
    }

    /**
     * @unreleased
     */
    public function attachShadowScript(): string
    {
        return "document.getElementById('app').attachShadow({mode: 'open'}).appendChild( document.getElementById('root') ).appendChild( document.getElementById('shadowDomStyles') )";
    }

    /**
     * Get main css path
     *
     * @unreleased
     */
    public function css(): string
    {
        return 'givewp-form-builder.css';
    }

    /**
     * Get main js path
     *
     * @unreleased
     */
    public function js(): string
    {
        return 'givewp-form-builder.js';
    }
}
