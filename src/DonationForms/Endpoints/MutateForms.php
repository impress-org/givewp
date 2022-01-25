<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */


abstract class MutateForms extends ListForms
{

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
        $result = $this->updateForms($parameters);
        $forms = $this->constructFormList($parameters);
        $response = array_merge((array)$forms, $result);

        return new WP_REST_Response(
            $response
        );
    }

    protected function updateForms($parameters)
    {
        $errors = 0;
        $successes = 0;
        $id_array = explode(',', $parameters['ids']);
        foreach($id_array as $id){
            if( is_numeric($id) && $this->mutate( $id ) ) {
                $successes++;
            }
            else
            {
                $errors++;
            }
        }
        return array( 'errors' => $errors, 'successes' => $successes );
    }

    abstract protected function mutate($id);
}
