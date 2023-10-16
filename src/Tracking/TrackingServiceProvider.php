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
     *
     * @since 3.0.0 Enable tracking if request is made by form builder
     */
    public function boot()
    {
        $isTrackingEnabled = Track::isTrackingEnabled();

        if ($isTrackingEnabled) {
            Hooks::addAction(TrackJobScheduler::CRON_JOB_HOOK_NAME, TrackJob::class, 'send');
        }

        // Enable telemetry for Visual Form Builder
        add_action('rest_api_init', function () use ($isTrackingEnabled) {
            $restRoute = $GLOBALS['wp']->query_vars['rest_route'] ?? '';
            if (empty($restRoute)) {
                return;
            }

            $isV3FormRoute = strpos($restRoute, 'givewp/v3/form') !== false;
            if ($isTrackingEnabled && $isV3FormRoute) {
                $this->enableTracking();
            }
        });

        if (is_admin()) {
            if ($isTrackingEnabled) {
                $this->enableTracking();
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
     * @since 3.0.0
     */
    private function enableTracking()
    {
        $this->registerTrackEvents();
        Hooks::addAction('shutdown', TrackJobScheduler::class, 'schedule', 999);
    }

    /**
     * Register track events.
     *
     * 'give_send_tracking_data' action hook that will be triggered track routine cron job.
     *
     * @since 3.0.0 Add support for v3 forms
     * @since 2.10.0
     */
    private function registerTrackEvents()
    {
        Hooks::addAction('givewp_form_builder_updated', EditedDonationFormsTracking::class, 'formBuilderUpdatedHookHandler');
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
