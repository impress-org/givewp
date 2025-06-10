<?php

namespace Give\DonationForms\OrphanedForms\Actions;

use Give\Helpers\EnqueueScript;

/**
 * @since 4.2.0
 */
class Assets
{
    /**
     * Enqueue scripts
     */
    public function __invoke()
    {
        if ('enabled' !== give_get_option('show_orphaned_forms_table', 'disabled')) {
            return;
        }

        EnqueueScript::make(
            'orphaned-forms-list-table',
            'build/assets/dist/js/orphaned-forms-list-table.js'
        )
            ->loadInFooter()
            ->registerTranslations()
            ->enqueue();
    }
}
