<?php

namespace Give\FeatureFlags\LegacyForms\Settings;

/**
 * @unreleased
 */
class Advanced
{
    /**
     * @unreleased
     */
    public function disableSections(array $sections): array
    {
        $disabledSectionIds = [
            //'stripe',
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
            //Advanced Options Section
            'css',
            'the_content_filter',
            'scripts_footer',
            Give()->routeForm->getOptionName(),
            // Stripe Section
            'stripe_js_fallback',
            'stripe_styles',

        ];

        foreach ($options as $key => $value) {
            if (isset($value['id']) && in_array($value['id'], $disabledOptionIds)) {
                unset($options[$key]);
            }
        }

        return $options;
    }
}
