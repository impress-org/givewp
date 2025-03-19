<?php

namespace Give\Campaigns\AsyncData\Actions;

/**
 * @unreleased
 */
class LoadAsyncDataAssets
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        LoadAsyncDataAssets::registerAssets();
        LoadAsyncDataAssets::enqueueAssets();
    }

    /**
     * @unreleased
     */
    public static function handleName(): string
    {
        return 'givewp-campaigns-async-data';
    }

    /**
     * @unreleased
     */
    public static function registerAssets()
    {
        wp_register_style(
            LoadAsyncDataAssets::handleName(),
            GIVE_PLUGIN_URL . 'build/assets/dist/css/give-campaigns-load-async-data.css',
            [],
            GIVE_VERSION
        );

        wp_register_script(
            LoadAsyncDataAssets::handleName(),
            GIVE_PLUGIN_URL . 'build/assets/dist/js/give-campaigns-load-async-data.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(LoadAsyncDataAssets::handleName(), 'GiveCampaignsAsyncData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'ajaxNonce' => wp_create_nonce('GiveCampaignsAsyncDataAjaxNonce'),
                'scriptDebug' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG,
                'throttlingEnabled' => ! defined('GIVE_ASYNC_DATA_THROTTLING') || GIVE_ASYNC_DATA_THROTTLING,
            ]
        );
    }

    /**
     * @unreleased
     */
    public static function enqueueAssets()
    {
        wp_enqueue_style(LoadAsyncDataAssets::handleName());
        wp_enqueue_script(LoadAsyncDataAssets::handleName());
    }
}
