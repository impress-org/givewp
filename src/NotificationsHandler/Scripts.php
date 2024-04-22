<?php

namespace Give\NotificationsHandler;


/**
 * @unreleased
 */
class Scripts
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        wp_register_script('give-notifications-handler', false);
        wp_enqueue_script('give-notifications-handler');

        wp_localize_script(
            'give-notifications-handler',
            'GiveNotifications',
            [
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );
    }
}
