<?php

namespace Give\API\Endpoints\DonationForms;

use Give_Payments_Query;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */


class ListDonations extends Endpoint
{
    protected $endpoint = '/admin/forms';

    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'page' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ],
            ],
        );
    }

    public function handleRequest( WP_REST_Request $request )
    {
        $parameters = $request->get_params();
        $forms = $this->constructFormList( $parameters );

        return new WP_REST_Response(
            $forms
        );
    }

    private function constructFormList( $parameters ) {
        $args = [
            // args here!
        ];
        $form_query = new Give_Forms_Query( $args );
        $forms = $form_query->get_forms();
        $result = array();
        // transform data
        return $result;
    }
}
