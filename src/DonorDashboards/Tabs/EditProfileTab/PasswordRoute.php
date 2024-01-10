<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use Give\DonorDashboards\Helpers\SanitizeProfileData as SanitizeHelper;
use Give\DonorDashboards\Profile as Profile;
use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;
use WP_REST_Request;

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
     * @since 3.3.0
     *
     * @param WP_REST_Request $request
     *
     * @return array
     *
     */
    public function handleRequest(WP_REST_Request $request)
    {
        wp_update_user([
            'ID' => wp_get_current_user()->ID,
            'user_pass' => $request->get_param('newPassword'),
        ]);

        return [
            'success' => true,
        ];
    }
}
