<?php
namespace Give\Helper\Donor;

/**
 * Get donor session.
 *
 * @since 2.7.0
 * @return array|string
 */
function getSession() {
	return Give()->session->get( 'give_purchase' );
}

/**
 * Store data to donor session.
 *
 * @since 2.7.0
 * @param string $key
 * @param mixed  $data
 * @param bool   $replace
 *
 * @return array|string
 */
function storeDataIntoSession( $key, $data, $replace = true ) {
	$session = getSession();

	if ( $replace ) {
		// Replace data.
		$session[ $key ] = $data;

	} elseif ( ! empty( $session[ $key ] ) && is_array( $data ) ) {
		// Merge data.
		$session[ $key ] = array_merge(
			(array) $session[ $key ],
			$data
		);

	} else {
		$session[ $key ] = $data;
	}

	return Give()->session->set( 'give_purchase', $session );
}
