<?php

namespace Give\DonationForms\AsyncData\Actions;

/**
 * @since 3.16.0
 */
class LoadAsyncDataAssets
{
    /**
     * @since 3.16.0
     */
    public function __invoke()
    {
        LoadAsyncDataAssets::registerAssets();
        LoadAsyncDataAssets::enqueueAssets();
    }

    /**
     * @since 3.16.0
     */
    public static function handleName(): string
    {
        return 'givewp-form-donations-async-data';
    }

    /**
     * @since 3.16.0
     */
    public static function registerAssets()
    {
        wp_register_style(
            LoadAsyncDataAssets::handleName(),
            GIVE_PLUGIN_URL . 'assets/dist/css/give-donation-forms-load-async-data.css',
            [],
            GIVE_VERSION
        );

        wp_register_script(
            LoadAsyncDataAssets::handleName(),
            GIVE_PLUGIN_URL . 'assets/dist/js/give-donation-forms-load-async-data.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(LoadAsyncDataAssets::handleName(), 'GiveDonationFormsAsyncData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'ajaxNonce' => wp_create_nonce('GiveDonationFormsAsyncDataAjaxNonce'),
                'scriptDebug' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG,
                'throttlingEnabled' => ! defined('GIVE_ASYNC_DATA_THROTTLING') || GIVE_ASYNC_DATA_THROTTLING,
            ]
        );
    }

    /**
     * @since 3.16.0
     */
    public static function enqueueAssets()
    {
        wp_enqueue_style(LoadAsyncDataAssets::handleName());
        wp_enqueue_script(LoadAsyncDataAssets::handleName());
    }
}
