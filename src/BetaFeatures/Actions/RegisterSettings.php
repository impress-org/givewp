<?php

namespace Give\BetaFeatures\Actions;

use Give\BetaFeatures\Facades\FeatureFlag;

/**
 * @since 3.6.0
 */
class RegisterSettings
{
    public function __invoke($settings)
    {
        if('beta' !== give_get_current_setting_section()) {
            return $settings;
        }

        FeatureFlag::resetNotificationCount();

        return $this->getSettings();
    }

    protected function getSettings()
    {
        return [
            ['id' => 'give_title_beta_features_1', 'type' => 'title'],
            ['id' => 'give_beta_features_banner', 'type' => 'beta_features'],
            [
                'name' => __('Event Tickets', 'give'),
                'desc' => __(
                    'If enabled, you’ll be get access to the event tickets feature where you can create events and sell tickets on your donation forms. Since this is in a beta, your feedback is crucial to help us improve and make the experience better before making it public.',
                    'give'
                ),
                'id' => 'enable_event_tickets',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'enabled' => __('Enabled', 'give'),
                    'disabled' => __('Disabled', 'give'),
                ],
            ],
            ['id' => 'give_title_beta_features_2', 'type' => 'sectionend'],
            ['id' => 'give_beta_features_feedback_link', 'type' => 'beta_features_feedback_link'],
        ];
    }
}
