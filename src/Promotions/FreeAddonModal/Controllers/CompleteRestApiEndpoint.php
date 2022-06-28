<?php

namespace Give\Promotions\FreeAddonModal\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CompleteRestApiEndpoint
{
    public function __invoke()
    {
        register_rest_route('give/v1', '/promotions/free-addon-modal/complete', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'handleModalCompletion'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args' => [
                'reason' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['subscribed', 'rejected'],
                ],
            ],

        ]);
    }

    public function handleModalCompletion(WP_REST_Request $request)
    {
        $reason = $request['reason'];
        $iteration = 1;

        if ( 'rejected' === $reason ) {
            // If the user has rejected the modal before, increase the iteration.
            $status = get_option('give_free_addon_modal_displayed');

            if ( !empty($status) ) {
                // The value will be something like rejected:1:1.18.0. The first number is the number of versions the modal has appeared
                // in, and the second number is the version number of the plugin at the time of last display.
                list($status, $iteration, $version) = explode(':', $status);

                $iteration++;
            }
        }

        update_option('give_free_addon_modal_displayed', implode(':', [
            $request['reason'],
            $iteration,
            GIVE_VERSION,
        ]));

        return new WP_REST_Response(['success' => true]);
    }
}
