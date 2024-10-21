<?php

namespace Give\BetaFeatures\Actions;

/**
 * @since 3.6.0
 */
class RegisterSettingSection
{
    /**
     * @since 3.6.0
     */
    public function __invoke($sections)
    {
        $sections['beta'] = apply_filters('givewp_settings_section_title_beta', __('Beta Features', 'give'));
        return $sections;
    }
}
