<?php

namespace Give\BetaFeatures;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     * @inheritDoc
     */
    public function register(): void
    {
        //
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        Hooks::addFilter('give_get_settings_general', Actions\RegisterSettings::class);
        Hooks::addFilter('give_get_sections_general', Actions\RegisterSettingSection::class);
    }
}
