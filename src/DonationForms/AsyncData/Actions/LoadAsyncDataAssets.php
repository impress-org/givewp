<?php

namespace Give\DonationForms\AsyncData\Actions;

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
        wp_enqueue_style(
            'givewp-form-donations-async-data',
            GIVE_PLUGIN_URL . 'assets/dist/css/give-donation-forms-load-async-data.css',
            [],
            GIVE_VERSION
        );

        wp_enqueue_script(
            'givewp-form-donations-async-data',
            GIVE_PLUGIN_URL . 'assets/dist/js/give-donation-forms-load-async-data.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script('givewp-form-donations-async-data', 'GiveDonationFormsAsyncData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'ajaxNonce' => wp_create_nonce('GiveDonationFormsAsyncDataAjaxNonce'),
            ]
        );
    }
}
