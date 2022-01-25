<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */


class DeleteForms extends MutateForms
{
    protected $endpoint = 'admin/forms/delete';

    public function permissionsCheck()
    {
        if ( ! current_user_can('delete_posts')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You dont have the right permissions to delete forms', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    protected function mutate($id)
    {
        return wp_delete_post($id, true);
    }
}
