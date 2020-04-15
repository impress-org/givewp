<?php
namespace Give\Helper\Session;

use function Give\Helpers\Form\Utils\isConfirmingDonation;

/**
 * Get donor session.
 *
 * @return array|string
 * @since 2.7.0
 */
function getSession() {
	return Give()->session->get( 'give_purchase' );
}

/**
 * Store data to donor session.
 *
 * @since 2.7.0
 * @param string $key
 * @param mixed  $data    Non null value. Key will be unset if value set to null
 * @param bool   $replace
 *
 * @return array|string
 */
function storeDataIntoSession( $key, $data, $replace = true ) {
	$session = getSession();

	if ( null === $data ) {
		unset( $session[ $key ] );

	} elseif ( $replace ) {
		// Replace data.
		$session[ $key ] = $data;

	} elseif ( ! empty( $session[ $key ] ) && is_array( $data ) ) {
		// Merge data.
		$session[ $key ] = array_merge(
			(array) $session[ $key ],
			(array) $data
		);

	} else {
		$session[ $key ] = $data;
	}

	return Give()->session->set( 'give_purchase', $session );
}

/**
 * Remove data from donor session.
 *
 * @since 2.7.0
 * @param $key
 */
function removeDataFromSession( $key ) {
	storeDataIntoSession( $key, null );
}
