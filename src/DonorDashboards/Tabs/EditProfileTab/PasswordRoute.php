<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use Give\Donors\Models\Donor;
use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.10.0
 */
class PasswordRoute extends RouteAbstract
{

    /**
     * @inheritdoc
     */
    public function endpoint()
    {
        return 'password';
    }

    /**
     * @inheritdoc
     */
    public function args()
    {
        return [
            'newPassword' => [
                'type' => 'string',
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }

    /**
     * Handles password update.
     *
     * @since 4.16.6 added password validation
     * @since 3.3.0
     *
     * @param WP_REST_Request $request
     *
     * @return array|WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $newPassword = trim($request->get_param('newPassword'));

        if (empty($newPassword)) {
            return new WP_REST_Response(
                [
                    'status' => 400,
                    'response' => 'invalid_password',
                    'body_response' => [
                        'message' => esc_html__('Please enter a valid password.', 'give'),
                    ],
                ]
            );
        }

        $donorId = give()->donorDashboard->getId();

        $donor = Donor::find($donorId);

        if ( ! $donor || empty($donor->userId)) {
            return new WP_REST_Response(
                [
                    'status' => 400,
                    'response' => 'no_user_account',
                    'body_response' => [
                        'message' => esc_html__('Unable to update password. Contact a site administrator.', 'give'),
                    ],
                ]
            );
        }

        wp_set_password($newPassword, $donor->userId);

        return [
            'success' => true,
        ];
    }
}
