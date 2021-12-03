<?php

namespace Give\DonationSummary;

use Give\Helpers\Hooks;

/**
 * @since 2.17.0
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider
{

    /**
     * @inheritDoc
     */
    public function register()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('give_pre_form_output', SummaryView::class);

        // Include the summary when changing payment gateways.
        if (wp_doing_ajax()) {
            add_action('give_donation_form', function ($formID) {
                $summary = new SummaryView();
                $summary->__invoke($formID);
                if ('give_donation_form_before_submit' === $summary->getFormTemplateLocation()) {
                    $summary->maybeRender();
                }
            });
        }

        Hooks::addAction('wp_enqueue_scripts', Assets::class, 'loadFrontendAssets');
    }
}
