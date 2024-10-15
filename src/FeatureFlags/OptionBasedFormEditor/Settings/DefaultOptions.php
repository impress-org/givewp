<?php

namespace Give\FeatureFlags\OptionBasedFormEditor\Settings;

/**
 * @unreleased
 */
class DefaultOptions extends AbstractOptionBasedFormEditorSettings
{
    /**
     * @unreleased
     */
    public function getNewDefaultSection(): string
    {
        return 'post-types';
    }
    
    /**
     * @unreleased
     */
    public function getDisabledSectionIds(): array
    {
        return [
            'display-settings',
            'taxonomies',
            'form-field-manager',
        ];
    }

    /**
     * @unreleased
     */
    public function getDisabledOptionIds(): array
    {
        return [
            //Post Types Section
            'form_featured_img',
            'featured_image_size',
            'form_sidebar',
            //Terms and Conditions Section
            'terms',
        ];
    }
}
