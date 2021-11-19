<?php

namespace Give\Tracking;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider;
use Give\Tracking\Events\DonationFormsTracking;
use Give\Tracking\Events\DonationMetricsTracking;
use Give\Tracking\Events\EditedDonationFormsTracking;
use Give\Tracking\Events\GivePluginSettingsTracking;
use Give\Tracking\Events\PluginsTracking;
use Give\Tracking\Events\ThemeTracking;
use Give\Tracking\Events\WebsiteTracking;
use Give\Tracking\Helpers\Track;

/**
 * Class TrackingServiceProvider
 * @package Give\Tracking
 *
 * @since 2.10.0
 */
class TrackingServiceProvider implements ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        give()->singleton(TrackRegisterer::class);
    }

    /**
     * @inheritdoc
     */
    public function boot()
    {
        $isTrackingEnabled = Track::isTrackingEnabled();

        if ($isTrackingEnabled) {
            Hooks::addAction(TrackJobScheduler::CRON_JOB_HOOK_NAME, TrackJob::class, 'send');
        }

        if (is_admin()) {
            if ($isTrackingEnabled) {
                $this->registerTrackEvents();
                Hooks::addAction('shutdown', TrackJobScheduler::class, 'schedule', 999);
            }

            if (Track::checkEnvironment()) {
                Hooks::addFilter('give_get_settings_advanced', AdminSettings::class, 'addSettings');
                Hooks::addAction('give_opt_in_into_tracking', AdminActionHandler::class, 'optInToUsageTracking');
                Hooks::addAction(
                    'give_hide_opt_in_notice_shortly',
                    AdminActionHandler::class,
                    'optOutFromUsageTracking'
                );
                Hooks::addAction(
                    'give_hide_opt_in_notice_permanently',
                    AdminActionHandler::class,
                    'optOutFromUsageTracking'
                );
                Hooks::addAction(
                    'update_option_give_settings',
                    AdminActionHandler::class,
                    'optInToUsageTrackingAdminGrantManually',
                    10,
                    2
                );
                Hooks::addAction('give_setup_page_before_sections', UsageTrackingOnBoarding::class, 'addNotice', 0);
                Hooks::addAction('admin_notices', UsageTrackingOnBoarding::class, 'addNotice');
            }
        }
    }

    /**
     * Register track events.
     *
     * 'give_send_tracking_data' action hook that will be triggered track routine cron job.
     *
     * @since 2.10.0
     */
    private function registerTrackEvents()
    {
        Hooks::addAction('save_post_give_forms', EditedDonationFormsTracking::class, 'savePostHookHandler');
        Hooks::addAction('save_post_give_payment', DonationFormsTracking::class, 'record');
        Hooks::addAction('save_post_give_payment', DonationMetricsTracking::class, 'record');
        Hooks::addAction('upgrader_process_complete', ThemeTracking::class, 'themeUpdateTrackingHandler', 10, 2);
        Hooks::addAction('shutdown', WebsiteTracking::class, 'websiteUpdateTrackingHandler');
        Hooks::addAction('update_option_give_settings', GivePluginSettingsTracking::class, 'record');
        Hooks::addAction('update_option_active_plugins', PluginsTracking::class, 'record');
        Hooks::addAction('switch_theme', ThemeTracking::class, 'record');
    }
}
