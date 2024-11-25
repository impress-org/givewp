<?php

namespace Give\FeatureFlags\OptionBasedFormEditor\Settings;

/**
 * @since 3.18.0
 */
class DefaultOptions extends AbstractOptionBasedFormEditorSettings
{
    /**
     * @since 3.18.0
     */
    public function getDisabledOptionIds(): array
    {
        return [
            // Form Fields Section
            'company_field',
            'last_name_field_required',
            'anonymous_donation',
            'donor_comment',
            //Post Types Section
            'form_featured_img',
            'featured_image_size',
            'form_sidebar',
            //Terms and Conditions Section
            'terms',
        ];
    }
}
