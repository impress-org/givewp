<?php

namespace Give\Onboarding;

/**
 * @since 2.8.0
 */
class SettingsRepositoryFactory
{

    /**
     * @since 2.8.0
     *
     * @param string $optionName
     *
     */
    public function make($optionName)
    {
        $option = get_option($optionName, []);

        $persistCallback = function ($settings) use ($optionName) {
            return update_option($optionName, $settings);
        };

        return new SettingsRepository($option, $persistCallback);
    }
}
