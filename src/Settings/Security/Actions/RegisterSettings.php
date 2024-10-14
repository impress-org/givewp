<?php

namespace Give\Settings\Security\Actions;

/**
 * @unreleased
 */
class RegisterSettings
{
    /**
     * @unreleased
     */
    public function __invoke(array $settings): array
    {
        if ('security' !== give_get_current_setting_section()) {
            return $settings;
        }

        return $this->getSettings();
    }

    /**
     * @unreleased
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
     * @unreleased
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
            'default' => 'disabled',
            'options' => [
                'enabled' => __('Enabled', 'give'),
                'disabled' => __('Disabled', 'give'),
            ],
        ];
    }
}
