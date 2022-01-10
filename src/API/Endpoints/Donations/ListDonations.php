<?php

namespace Give\API\Endpoints\Donations;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */


class ListDonations extends Endpoint
{
    protected $endpoint = '/donations';

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
        $payments = $this->constructPaymentList( $parameters );

        return new WP_REST_Response(
            $payments
        );
    }

    private function constructPaymentList( $parameters ) {
        $args = [
            'output'     => 'payments',
            'number'     => 20,
            'page'       => isset( $parameters['page'] ) ? $parameters['page'] : null,
        ];
        $payment_query = new \Give_Payments_Query( $args );
        $payments = $payment_query->get_payments();

        foreach ($payments as $payment) {
            $payment->id = $payment->ID;
            $payment_meta = give_get_payment_meta( $payment->ID );
            $donor_id           = give_get_payment_donor_id( $payment->ID );
            $donor_name         = give_get_donor_name_by( $donor_id, 'donor' );
            $payment->donorName = $donor_name;
            $payment->donationForm = $payment_meta['_give_payment_form_title'];
            $payment->donationStatus = give_get_payment_status( $payment->ID, true );
            $payment->datetime = $payment_meta['date'];
            $payment->amount = html_entity_decode( give_donation_amount( $payment, true ) );
            $payment->details = esc_url( add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details' ) ) );
            $payment->paymentType = '';
        }
        return $payments;
    }
}

/*
 * Sample usage (JS):
        fetch('/wp-json/give-api/v2/donations/?page=2', {
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.GiveDonations.apiNonce,
            }
        })
            .then(res => (res.json()))
            .then(res => console.log(res))
            .catch(err => console.log(err.message));
 */
