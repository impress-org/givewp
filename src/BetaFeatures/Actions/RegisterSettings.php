<?php

namespace Give\BetaFeatures\Actions;

/**
 * @unreleased
 */
class RegisterSettings
{
    public function __invoke($settings)
    {
        if('beta' !== give_get_current_setting_section()) {
            return $settings;
        }

        return $this->getSettings();
    }

    protected function getSettings()
    {
        return [
            ['id' => 'give_title_beta_features_1', 'type' => 'title'],
            [
                'name' => __('Event Tickets', 'give'),
                'desc' => __(
                    'If enabled, you’ll be get access to the event tickets feature where you can create and manage events, and link them to your donation form. Since this is in a beta, your feedback is crucial to help us improve and make the experience better before making it public.',
                    'give'
                ),
                'id' => 'enable_event_tickets',
                'type' => 'radio_inline',
                'default' => 'enabled',
                'options' => [
                    'enabled' => __('Enabled', 'give'),
                    'disabled' => __('Disabled', 'give'),
                ],
            ],
            ['id' => 'give_title_beta_features_2', 'type' => 'sectionend'],
        ];
    }
}
