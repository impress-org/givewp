<?php

namespace Give\EventTickets\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class EnqueueDonationFormScripts
{
    /**
     * @unreleased
     */
    public function __invoke(): void
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/eventTicketsTemplate.asset.php');

        wp_enqueue_script(
            'givewp-event-tickets-template',
            GIVE_PLUGIN_URL . 'build/eventTicketsTemplate.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_style(
            'givewp-event-tickets-template',
            GIVE_PLUGIN_URL . 'build/eventTicketsTemplate.css',
            [],
            $scriptAsset['version']
        );
    }
}
