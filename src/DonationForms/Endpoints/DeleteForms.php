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


    protected function mutate($id)
    {
        return wp_delete_post($id, true);
    }
}
