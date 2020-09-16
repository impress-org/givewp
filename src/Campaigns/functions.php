<?php


function give_campaings_get_aggregate_total() {

	$forms = get_posts(
		[
			'post_type' => 'give_forms',
			'tax_query' => [
				[
					'taxonomy' => 'give_campaign',
					'field'    => 'term_id',
					'terms'    => 33,
				],
			],
		]
	);

	$formIDs = array_map(
		function( $form ) {
			return $form->ID;
		},
		$forms
	);

	$payments = ( new \Give_Payments_Query(
		[
			'give_forms' => array_merge( $formIDs, [ 999 ] ),
			'status'     => [ 'publish' ],
			'number'     => - 1,
			'fields'     => 'ids',
		]
	) )->get_payments();

	return array_reduce(
		$payments,
		function( $total, $payment ) {
			return $total + $payment->total;
		},
		0
	);
}

function give_campaings_get_aggregate_total_query() {
	global $wpdb;

	/**
	 * Get the SUM of the payment totals for published donations of a given set of forms.
	 */
	$results = $wpdb->get_row(
		"
        SELECT
            sum( meta_value ) as total,
            count( meta_value ) as count,
            avg( meta_value ) as average
        FROM wp_posts as payment
            JOIN wp_give_donationmeta as meta
                ON meta.donation_id = payment.ID
        WHERE
            payment.post_type = 'give_payment'
            AND
            meta.meta_key = '_give_payment_total'
            AND
            payment.post_status = 'publish'
            AND
            payment.ID IN (
                SELECT donation_id  
                FROM wp_give_donationmeta
                WHERE meta_key = '_give_payment_form_id'
                AND meta_value IN ( 66, 83, 999 )
            )
    "
	);

	return $results;
}
