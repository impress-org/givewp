<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */


class TrashForms extends ListForms
{
    protected $endpoint = '/admin/forms';

    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'DELETE',
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

    public function handleRequest(WP_REST_Request $request)
    {
        $parameters = $request->get_params();
        $errors = $this->updateForms($parameters);
        $forms = $this->constructFormList($parameters);
        $forms->errors = $errors;

        return new WP_REST_Response(
            $forms
        );
    }

    protected function updateForms($parameters)
    {
        $errors = 0;
        $id_array = explode(',', $parameters['ids']);
        foreach($id_array as $id){
            if( !is_numeric($id) || !wp_trash_post( $id, true ) ) {
                $errors++;
            }
        }
        return $errors;
    }
}
