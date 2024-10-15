<?php

namespace Give\Settings\Security\Actions;

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
        $sections['security'] = __('Security', 'give');

        return $sections;
    }
}
