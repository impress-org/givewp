<?php

namespace Give\DonationForms\OrphanedForms\Actions;

use Give\Helpers\EnqueueScript;

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
        if ('enabled' !== give_get_option('show_orphaned_forms_table')) {
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
