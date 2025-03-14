<?php

namespace Give\Settings\Security\Actions;

/**
 * @since 3.17.0
 */
class RegisterSection
{
    /**
     * @since 3.17.0
     */
    public function __invoke(array $sections): array
    {
        $sections['security'] = __('Security', 'give');

        return $sections;
    }
}
