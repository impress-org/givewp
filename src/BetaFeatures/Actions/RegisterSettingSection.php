<?php

namespace Give\BetaFeatures\Actions;

/**
 * @unreleased
 */
class RegisterSettingSection
{
    public function __invoke($sections)
    {
        $sections['beta'] = __('Beta Features', 'give');
        return $sections;
    }
}
