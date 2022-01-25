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

    protected function mutate($id)
    {
        return wp_trash_post($id, true);
    }
}
