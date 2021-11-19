<?php

namespace Give\Tracking;

use Give\Onboarding\Setup\PageView;
use Give\Tracking\Repositories\Settings;
use Give_Admin_Settings;

/**
 * Class OnBoarding
 * @package Give\Tracking
 *
 * This class uses to setup notice nag to website administrator if admin is not opt in for usage tracking and gives admin an option to directly opt-in.
 *
 * @since 2.10.0
 */
class UsageTrackingOnBoarding
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * UsageTrackingOnBoarding constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Register notice.
     *
     * @since 2.10.0
     */
    public function addNotice()
    {
        if ( ! $this->canShowNotice()) {
            return;
        }

        echo $this->getNotice(true);
    }

    /**
     * Get option name of notice.
     *
     * We use this option key to disable notice nag for specific user for a interval.
     *
     * @since 2.10.0
     * @return string
     */
    public function getNoticeOptionKey()
    {
        return give()->notices->get_notice_key('usage-tracking-nag', 'permanent');
    }

    /**
     * Get notice.
     *
     * @since 2.10.0
     *
     * @param bool $wrapper
     *
     * @return string
     */
    public function getNotice($wrapper = false)
    {
        /* @var PageView $pageView */
        $pageView = give()->make(PageView::class);

        $notice = $pageView->render_template(
            'row-item',
            [
                'icon' => $pageView->image('hands-in.svg'),
                'class' => ! $wrapper ? 'usage-tracking' : '',
                'icon_alt' => esc_html__('Anonymous usage tracking icon', 'give'),
                'title' => esc_html__('Help GiveWP improve your fundraising experience', 'give'),
                'description' => sprintf(
                    '%1$s<br><br><a href="https://go.givewp.com/plugin-optin" class="learn-more-link" target="_blank">%2$s</a>',
                    esc_html__(
                        'Can you help improve GiveWP? With your permission, the GiveWP team uses non-sensitive data from plugin users to optimize donation form conversion rates, increase average donation amounts, and streamline the fundraising experience. This data is never shared with any third party.',
                        'give'
                    ),
                    esc_html__(
                        'Learn more about how GiveWP respects user and donor privacy while improving the plugin >',
                        'give'
                    )
                ),
                'action' => sprintf(
                    '<a class="button" href="%1$s">%2$s</a><div class="sub-links"><a href="%3$s" title="%7$s">%4$s</a><a href="%5$s">%6$s</a></div>',
                    add_query_arg(['give_action' => 'opt_in_into_tracking']),
                    esc_html__('Glad to Help', 'give'),
                    add_query_arg(['give_action' => 'hide_opt_in_notice_shortly']),
                    esc_html__('Not Right Now', 'give'),
                    add_query_arg(['give_action' => 'hide_opt_in_notice_permanently']),
                    esc_html__('Dismiss Forever', 'give'),
                    esc_html__('Disable notice for 48 hours', 'give')
                ),
            ]
        );

        return $wrapper ? sprintf(
            '<div class="usage-tracking notice"><section><main>%1$s</main></section></div>',
            $notice
        ) : $notice;
    }

    /**
     * Return whether or not user can see notice.
     *
     * @since 2.10.0
     */
    public function canShowNotice()
    {
        if ( ! current_user_can('manage_give_settings')) {
            return false;
        }

        $section = isset($_GET['section']) ? 'advanced-options' : '';
        if (Give_Admin_Settings::is_setting_page('advanced', $section)) {
            return false;
        }

        $optionValue = $this->settings->getUsageTrackingNoticeNagOptionValue();

        if (is_numeric($optionValue) && ('0' === $optionValue || $optionValue > time())) {
            return false;
        }

        return ! give_is_setting_enabled($this->settings->getUsageTrackingOptionValue());
    }

    /**
     * Disable notice.
     *
     * @since 2.10.0
     *
     * @param int $timestamp
     *
     * @return bool
     */
    public function disableNotice($timestamp)
    {
        return $this->settings->saveUsageTrackingNoticeNagOptionValue($timestamp);
    }
}
