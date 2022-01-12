<?php

namespace Give\API\Endpoints\DonationForms;

use Give_Donate_Form;
use Give_Forms_Query;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */


class ListForms extends Endpoint
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
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'int',
                        'required' => false,
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
        $args = array(
                'number'      => 30,
        );
        $form_query = new Give_Forms_Query( $args );
        $forms = $form_query->get_forms();
        $results = array();
        foreach( $forms as $form ) {
            $result = new Give_Donate_Form($form->ID);
            //if there are multiple prices, get the highest and lowest
            if( is_array( $result->prices ) ) {
                $all_prices = array_column($result->prices, '_give_amount');
                $prices = array(
                    min($all_prices),
                    max($all_prices)
                );
            }
            $results[] = (object)[
                'id' => $form->ID,
                'name' => $result->post_title,
                'amount' => $prices ?: $result->price,
                'goal' => $result->goal,
                'donations' => count( give_get_payments( ['give_forms' => $form->ID ] ) ),
                'datetime' => $result->post_date,
                'shortcode' => "[give_form id=\"$form->ID\"]"
            ];
        }
        return $results;
    }
}
