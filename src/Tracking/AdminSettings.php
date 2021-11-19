<?php

namespace Give\Tracking;

use Give\Tracking\Repositories\Settings;
use Give_Admin_Settings;

/**
 * Class AdminSettings
 * @package Give\Tracking
 *
 * @since 2.10.0
 */
class AdminSettings
{
    /**
     * Add admin settings.
     *
     * @since 2.10.0
     *
     * @param array $settings
     *
     * @return mixed
     */
    public function addSettings($settings)
    {
        if ( ! Give_Admin_Settings::is_setting_page('advanced', 'advanced-options')) {
            return $settings;
        }

        array_splice(
            $settings,
            -2,
            0,
            [
                [
                    'name' => __('Anonymous Usage Tracking', 'give'),
                    'label' => esc_html__('Allow usage of GiveWP to be tracked.', 'give'),
                    'desc' => esc_html__(
                        'Can GiveWP collect data about the usage of the plugin? Usage data is completely anonymous, does not include any personal information, and will only be used to improve the software.',
                        'give'
                    ),
                    'id' => Settings::USAGE_TRACKING_OPTION_KEY,
                    'type' => 'radio_inline',
                    'default' => 'disabled',
                    'options' => [
                        'enabled' => esc_html__('Enabled', 'give'),
                        'disabled' => esc_html__('Disabled', 'give'),
                    ],
                ],
            ]
        );

        return $settings;
    }
}
