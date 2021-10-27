<?php

namespace Give\DonationSummary;

use Give\Helpers\Hooks;

/**
 * @since 2.10.0
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider {

    /**
     * @inheritDoc
     */
    public function register() {
        //
    }

    /**
     * @inheritDoc
     */
    public function boot() {

        /**
         * @hook give_donation_form_user_info
         * @hook give_donation_form_before_submit
         */
        Hooks::addAction( 'give_donation_form_before_submit', SummaryView::class );

        Hooks::addAction( 'wp_enqueue_scripts', Assets::class, 'loadFrontendAssets' );
    }
}
