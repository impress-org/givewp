<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */


class TrashForms extends MutateForms
{
    protected $endpoint = 'admin/forms/trash';

    /**
     * Check user permissions
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('delete_posts')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You dont have the right permissions to trash forms', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    protected function mutate($id)
    {
        return wp_trash_post($id, true);
    }
}
