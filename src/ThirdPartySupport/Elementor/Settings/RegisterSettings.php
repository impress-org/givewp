<?php

namespace Give\ThirdPartySupport\Elementor\Settings;

/**
 * @since 4.7.0
 */
class RegisterSettings
{
    /**
     * @since 4.7.0
     */
    public function __invoke(array $settings): array
    {
        if ('elementor' !== give_get_current_setting_section()) {
            return $settings;
        }

        return $this->getSettings();
    }

    /**
     * @since 4.7.0
     */
    protected function getSettings(): array
    {
        return [
            [
                'id' => 'give_title_settings_elementor_1',
                'type' => 'title',
            ],
            $this->getElementorSettings(),
            [
                'id' => 'give_title_settings_elementor_1',
                'type' => 'sectionend',
            ],
        ];
    }

    /**
     * @since 4.7.0
     */
    public function getElementorSettings(): array
    {
        return [
            'name' => __('Enable Elementor Legacy Widgets', 'give'),
            'desc' => __('If enabled, this option will add legacy widgets to elementor that were previously available in the GiveWP Elementor Widgets plugin.', 'give'),
            'id' => 'givewp_elementor_legacy_widgets_enabled',
            'type' => 'radio_inline',
            'default' => 'disabled',
            'options' => [
                'enabled' => __('Enabled', 'give'),
                'disabled' => __('Disabled', 'give'),
            ],
        ];
    }
}
