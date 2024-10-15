<?php

namespace Give\FeatureFlags\OptionBasedFormEditor\Settings;

/**
 * @unreleased
 */
class Advanced extends AbstractOptionBasedFormEditorSettings
{
    /**
     * @unreleased
     */
    public function getNewDefaultSection(): string
    {
        return '';
    }

    /**
     * @unreleased
     */
    public function getDisabledSectionIds(): array
    {
        return [];
    }

    /**
     * @unreleased
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
