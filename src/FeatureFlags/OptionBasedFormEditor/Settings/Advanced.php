<?php

namespace Give\FeatureFlags\OptionBasedFormEditor\Settings;

/**
 * @since 3.18.0
 */
class Advanced extends AbstractOptionBasedFormEditorSettings
{
    /**
     * @since 3.18.0
     */
    public function getDisabledOptionIds(): array
    {
        return [
            //Advanced Options Section
            'css',
            'the_content_filter',
            'scripts_footer',
            Give()->routeForm->getOptionName(),
            // Stripe Section
            'stripe_js_fallback',
            'stripe_styles',
        ];
    }
}
