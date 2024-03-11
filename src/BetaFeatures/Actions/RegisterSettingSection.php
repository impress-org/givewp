<?php

namespace Give\BetaFeatures\Actions;

use Give\BetaFeatures\Facades\FeatureFlag;

/**
 * @unreleased
 */
class RegisterSettingSection
{
    public function __invoke($sections)
    {
        $sections['beta'] = apply_filters('givewp_settings_section_title_beta', __('Beta Features', 'give'));
        return $sections;
    }
}
