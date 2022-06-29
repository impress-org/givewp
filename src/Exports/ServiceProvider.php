<?php

namespace Give\Exports;

/**
 * @since 2.21.2
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
            class_alias(DonorsExport::class, 'Give_Donors_Export');
        });

        add_action( 'give_tools_tab_export_after_donation_history', static function() {
            include 'resources/views/export-donors-table-row.php';
        });
    }
}
