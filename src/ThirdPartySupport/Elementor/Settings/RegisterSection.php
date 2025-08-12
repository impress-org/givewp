<?php

namespace Give\ThirdPartySupport\Elementor\Settings;

/**
 * @unreleased
 */
class RegisterSection
{
    /**
     * @unreleased
     */
    public function __invoke(array $sections): array
    {
        $sections['elementor'] = __('Elementor', 'give');

        return $sections;
    }
}
