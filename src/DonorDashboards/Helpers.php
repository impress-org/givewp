<?php

namespace Give\DonorDashboards;

/**
 * @since 2.10.0
 */
class Helpers {

	/**
	 * Retrieve the current donor ID from based on session
	 * @since 2.10.0
	 */
	public static function getCurrentDonorId() {

		if ( get_current_user_id() ) {
			$donor = give()->donors->get_donor_by( 'user_id', get_current_user_id() );
			if ( $donor ) {
				return $donor->id;
			}
		}

		if ( give()->email_access ) {
			give()->email_access->init();
			$useToken = give()->email_access->check_for_token();

			if ( $useToken ) {
				$donor = give()->donors->get_donor_by( 'email', give()->email_access->token_email );
				return $donor->id;
			}
		}

		if (
			false !== give()->session->get_session_expiration() ||
			true === give_get_history_session()
		) {
			$email = give()->session->get( 'give_email' );
			$donor = give()->donors->get_donor_by( 'email', $email );
			if ( $donor ) {
				return $donor->id;
			}
		}

		return null;
	}
}
