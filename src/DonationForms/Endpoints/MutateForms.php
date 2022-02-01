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
                    'search' => [
                        'type' => 'string',
                        'required' => 'false',
                        'validate_callback' => [$this, 'validateSearch'],
                        'sanitize_callback' => [$this, 'sanitizeSearch']
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

    public function validateIDString($param, $request, $key)
    {
        $param_array = explode(',', $param);
        foreach($param_array as $id){
            if(!$this->validateInt($id, $request, $key))
            {
                return false;
            }
        }
        return true;
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
            $this->mutate( $id ) ? $successes++ : $errors++;
        }
        return array( 'errors' => $errors, 'successes' => $successes );
    }

    abstract protected function mutate($id);
}
