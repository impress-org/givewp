<?php

namespace Give\Notifications;


/**
 * @unreleased
 */
class Assets
{
    /**
     * @unreleased
     */
    public function __invoke(): void
    {
        wp_register_script('give-notifications-handler', false);
        wp_enqueue_script('give-notifications-handler');

        wp_localize_script(
            'give-notifications-handler',
            'givewpNotifications',
            [
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );
    }
}
