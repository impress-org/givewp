<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

require_once( GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');

/**
 * @unreleased
 */


class DuplicateForms extends MutateForms
{
    protected $endpoint = 'admin/forms/duplicate';

    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'int',
                        'required' => false,
                    ],
                    'perPage' => [
                        'type' => 'int',
                        'required' => false,
                    ],
                    'ids' => [
                        'type' => 'string',
                        'required' => true
                    ]
                ],
            ],
        );
    }

    protected function mutate($id)
    {
        return \Give_Form_Duplicator::handler($id);
    }
}
