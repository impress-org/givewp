<?php

namespace Give\Exports;

use Give\Helpers\Hooks;

/**
 * @since 1.3.0
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider {

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
        /** @note GiveWP Batch Exporting expects an un-namespaced class name. */
        add_action( 'give_batch_export_class_include', function() {
            class_alias(DonorsByDonationExport::class, 'Give_Donors_By_Donation_Export');
        });

        add_action( 'give_tools_tab_export_after_donors', static function() {
            include 'resources/views/donors-by-donation-table-row.php';
        });
    }
}
