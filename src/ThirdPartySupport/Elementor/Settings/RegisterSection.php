<?php

namespace Give\ThirdPartySupport\Elementor\Settings;

/**
 * @since 4.7.0
 */
class RegisterSection
{
    /**
     * @since 4.7.0
     */
    public function __invoke(array $sections): array
    {
        $sections['elementor'] = __('Elementor', 'give');

        return $sections;
    }
}
