<?php

use Give\Framework\PaymentGateways\PaymentGatewayRegister;

/**
 * Insert donor comment to donation.
 *
 * @since 2.21.0 remove anonymous
 * @since 2.2.0
 *
 * @param  int  $donation_id
 * @param  array  $donation_data
 */
function __give_insert_donor_donation_comment( $donation_id, $donation_data ) {
	if ( ! empty( $_POST['give_comment'] ) ) {
        $donation = give()->donations->getById($donation_id);
        $donation->comment = sanitize_textarea_field(trim($_POST['give_comment']));
        $donation->save();
    }
}

add_action( 'give_insert_payment', '__give_insert_donor_donation_comment', 10, 2 );



/**
 * Validate donor comment
 *
 * @since 2.2.0
 */
function __give_validate_donor_comment() {
	// Check wp_check_comment_data_max_lengths for comment length validation.
	if ( ! empty( $_POST['give_comment'] ) ) {
		$max_lengths = wp_get_comment_fields_max_lengths();
		$comment     = give_clean( $_POST['give_comment'] );

		if ( mb_strlen( $comment, '8bit' ) > $max_lengths['comment_content'] ) {
			give_set_error( 'comment_content_column_length', __( 'Your comment is too long.', 'give' ) );
		}
	}
}
add_action( 'give_checkout_error_checks', '__give_validate_donor_comment', 10, 1 );


/**
 * Update donor comment status when donation status update
 *
 * @since 2.2.0
 *
 * @param $donation_id
 * @param $status
 */
function __give_update_donor_donation_comment_status( $donation_id, $status ) {
	$approve = absint( 'publish' === $status );

	/* @var WP_Comment $note */
	$donor_comment = give_get_donor_donation_comment( $donation_id, give_get_payment_donor_id( $donation_id ) );

	if ( $donor_comment instanceof WP_Comment ) {
		wp_set_comment_status( $donor_comment->comment_ID, (string) $approve );
	}
}

add_action( 'give_update_payment_status', '__give_update_donor_donation_comment_status', 10, 2 );

/**
 * Remove donor comment when donation delete
 *
 * @since 2.2.0
 *
 * @param $donation_id
 */
function __give_remove_donor_donation_comment( $donation_id ) {
	/* @var WP_Comment $note */
	$donor_comment = give_get_donor_donation_comment( $donation_id, give_get_payment_donor_id( $donation_id ) );

	if ( $donor_comment instanceof WP_Comment ) {
		wp_delete_comment( $donor_comment->comment_ID );
	}
}

add_action( 'give_payment_deleted', '__give_remove_donor_donation_comment', 10 );


/**
 * Update anonymous donation for legacy gateways
 *
 * @since 2.21.3
 *
 * @retrun void
 */
function giveUpdateAnonymousDonationForLegacyGateways(int $donationId)
{
    $gatewayId = give_get_meta($donationId,'_give_payment_gateway', true);

    /** @var PaymentGatewayRegister $registrar */
    $registrar = give(PaymentGatewayRegister::class);

    if (!$registrar->hasPaymentGateway($gatewayId)){
        $isAnonymousDonation = isset( $_POST['give_anonymous_donation'] ) ? absint( give_clean($_POST['give_anonymous_donation']) ) : 0;

        give_update_meta( $donationId, '_give_anonymous_donation', $isAnonymousDonation );
    }
}

add_action('give_insert_payment', 'giveUpdateAnonymousDonationForLegacyGateways');
