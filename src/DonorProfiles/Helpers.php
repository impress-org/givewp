<?php

namespace Give\DonorProfiles;

/**
 * @since 2.10.0
 */
class Helpers {

	/**
	 * Retrieve the current donor ID from based on session
	 * @since 2.10.0
	 */
	public static function getCurrentDonorId() {

		give()->email_access->init();
		$useToken = give()->email_access->check_for_token();

		if ( $useToken ) {
			$donor = give()->donors->get_donor_by( 'email', give()->email_access->token_email );
			return $donor->id;
		}

		if ( get_current_user_id() ) {
			$donor = give()->donors->get_donor_by( 'user_id', get_current_user_id() );
			if ( $donor ) {
				return $donor->id;
			}
		}

		return null;
	}
}
