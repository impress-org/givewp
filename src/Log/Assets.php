<?php

namespace Give\Log;

use Give\Helpers\EnqueueScript;
use Give\Log\ValueObjects\LogType;

/**
 * Class Assets
 * @package Give\Log\UserInterface
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
        $data =  [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/logs')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'logTypes' => LogType::getTypesTranslated()
        ];

        EnqueueScript::make(
            'give-admin-log-list-table-app',
            'assets/dist/js/give-log-list-table-app.js'
        )->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveLogs', $data)
            ->enqueue();
    }
}
