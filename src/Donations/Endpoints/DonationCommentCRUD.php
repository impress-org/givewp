<?php

namespace Give\Donations\Endpoints;

use Give\Donations\Endpoints\DonationUpdateAttributes\IdAttribute;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class DonationCommentCRUD extends Endpoint
{
    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            '/admin/donation/(?P<id>[\d]+)/comment',
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'handleEditableRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [
                        'id' => IdAttribute::getDefinition(),
                        'content' => [
                            'type' => 'string',
                            'required' => true,
                            'sanitize_callback' => 'wp_kses_post',
                        ],
                    ],
                ],
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'handleDeletableRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [
                        'id' => IdAttribute::getDefinition(),
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('edit_give_payments')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You don\'t have permission to edit Donations', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function handleEditableRequest(WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        $donation = give()->donations->getById($id);

        try {
            $donation->comment = $request->get_param('content');
            $donation->save();
        } catch (Exception $e) {
            return new WP_Error(
                'donation_comment_update_failed',
                esc_html__('Failed to update donation comment', 'give'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
            ]
        );
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function handleDeletableRequest(WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        $donation = give()->donations->getById($id);

        try {
            $donation->comment = '';
            $donation->save();
        } catch (Exception $e) {
            return new WP_Error(
                'donation_comment_delete_failed',
                esc_html__('Failed to delete donation comment', 'give'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
            ]
        );
    }
}
