<?php

namespace Give\MigrationLog\Helpers;

use Give\Helpers\EnqueueScript;

/**
 * Class Assets
 * @package Give\MigrationLog\Helpers
 *
 * @since 2.10.0
 */
class Assets
{
    /**
     * Enqueue scripts
     */
    public function enqueueScripts()
    {
        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/migrations')),
            'apiNonce' => wp_create_nonce('wp_rest'),
        ];

        EnqueueScript::make('give-migrations-list-table-app', 'assets/dist/js/give-migrations-list-table-app.js')
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveMigrations', $data)
            ->enqueue();
    }
}
