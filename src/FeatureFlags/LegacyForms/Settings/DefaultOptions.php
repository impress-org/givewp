<?php

namespace Give\FeatureFlags\LegacyForms\Settings;

/**
 * @unreleased
 */
class DefaultOptions
{
    public function setDefaultTab()
    {
        return 'post-types';
    }

    /**
     * @unreleased
     */
    public function disableSections(array $sections): array
    {
        $disabledSectionIds = [
            'display-settings',
            'taxonomies',
            'form-field-manager',
        ];

        foreach ($sections as $key => $value) {
            if (in_array($key, $disabledSectionIds)) {
                unset($sections[$key]);
            }
        }

        return $sections;
    }


    /**
     * @unreleased
     */
    public function disableOptions(array $options): array
    {
        $disabledOptionIds = [
            //Post Types Section
            'form_featured_img',
            'featured_image_size',
            'form_sidebar',
            //Terms and Conditions Section
            'terms',
        ];

        foreach ($options as $key => $value) {
            if (isset($value['id']) && in_array($value['id'], $disabledOptionIds)) {
                unset($options[$key]);
            }
        }

        return $options;
    }
}
