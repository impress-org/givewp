<?php

namespace Give\BetaFeatures\Actions;

/**
 * @unreleased
 */
class RegisterSettingSection
{
    /**
     * @unreleased
     */
    public function __invoke($sections)
    {
        $sections['beta'] = apply_filters('givewp_settings_section_title_beta', __('Beta Features', 'give'));
        return $sections;
    }
}
