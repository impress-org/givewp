<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

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
                        'validate_callback' => [$this, 'validateInt'],
                    ],
                    'perPage' => [
                        'type' => 'int',
                        'required' => false,
                        'validate_callback' => [$this, 'validateInt'],
                    ],
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateStatus']
                    ],
                    'ids' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validateIDString'],
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
