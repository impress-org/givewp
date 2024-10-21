<?php

namespace Give\Settings\Security\Actions;

/**
 * @since 3.17.0
 */
class RegisterSettings
{
    /**
     * @since 3.17.0
     */
    public function __invoke(array $settings): array
    {
        if ('security' !== give_get_current_setting_section()) {
            return $settings;
        }

        return $this->getSettings();
    }

    /**
     * @since 3.17.0
     */
    protected function getSettings(): array
    {
        return [
            [
                'id' => 'give_title_settings_security_1',
                'type' => 'title',
            ],
            $this->getHoneypotSettings(),
            [
                'id' => 'give_title_settings_security_1',
                'type' => 'sectionend',
            ],
        ];
    }

    /**
     * @since 3.17.1 enable by default
     * @since 3.17.0
     */
    public function getHoneypotSettings(): array
    {
        return [
            'name' => __('Enable Honeypot Field', 'give'),
            'desc' => __(
                'If enabled, this option will add a honeypot security measure to all donation forms',
                'give'
            ),
            'id' => 'givewp_donation_forms_honeypot_enabled',
            'type' => 'radio_inline',
            'default' => 'enabled',
            'options' => [
                'enabled' => __('Enabled', 'give'),
                'disabled' => __('Disabled', 'give'),
            ],
        ];
    }
}
