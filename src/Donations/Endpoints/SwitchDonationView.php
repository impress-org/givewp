<?php

namespace Give\Donations\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

class SwitchDonationView extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations/view';

    /**
     * @var string
     */
    protected $slug = 'donations';

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'isLegacy' => [
                        'type' => 'boolean',
                        'required' => true,
                    ]
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     * @since 2.20.0
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $success = true;
        $isLegacyEnabled = $request->get_param('isLegacy');
        $userId = get_current_user_id();
        if($userId)
        {
            update_user_meta($userId, sprintf('_give_%s_archive_show_legacy', $this->slug), $isLegacyEnabled);
        }
        else
        {
            $success = false;
        }

        return new WP_REST_Response(
            $success
        );
    }
}
