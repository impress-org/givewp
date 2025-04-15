<?php

namespace Give\DonationForms\OrphanedForms\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class Assets
{
    /**
     * Enqueue scripts
     */
    public function __invoke()
    {
        if ( 'enabled' !== give_get_option('show_orphaned_forms_table')) {
            return;
        }

        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/orphanedFormsListTable.asset.php');

        wp_enqueue_script(
            'givewp-orphaned-forms-list-table',
            GIVE_PLUGIN_URL . 'build/orphanedFormsListTable.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );
    }
}
