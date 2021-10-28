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

        Hooks::addAction( 'give_pre_form_output', SummaryView::class );

        Hooks::addAction( 'wp_enqueue_scripts', Assets::class, 'loadFrontendAssets' );
    }
}
