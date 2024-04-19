<?php

namespace Give\NotificationsHandler;


/**
 * @unreleased
 */
class Assets
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        wp_localize_script(
            'give-admin-scripts',
            'GiveNotifications',
            [
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );
    }
}
