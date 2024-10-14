<?php

namespace Give\Settings\Security\Actions;

use Give\Settings\Security\SecuritySettingsPage;

/**
 * @unreleased
 */
class RegisterPage
{
    /**
     * @unreleased
     */
    public function __invoke(array $settingsPages): array
    {
        $settingsPages[] = new SecuritySettingsPage();

        return $settingsPages;
    }
}
