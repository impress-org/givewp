<?php

namespace Give\Settings\Security\Actions;

use Give\Settings\Security\SecuritySettingsPage;

/**
 * @since 3.17.0
 */
class RegisterPage
{
    /**
     * @since 3.17.0
     */
    public function __invoke(array $settingsPages): array
    {
        $settingsPages[] = new SecuritySettingsPage();

        return $settingsPages;
    }
}
