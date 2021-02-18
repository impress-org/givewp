<?php

namespace Give\DonorProfiles;

use Give\DonorProfiles\Profile;
use Give\DonorProfiles\Helpers\LocationList;
use Give\DonorProfiles\Helpers;

class App {

	/**
	 * Get output markup for Donor Profile app
	 *
	 * @return string
	 * @since 2.10.0
	 **/
	public function getOutput() {
		ob_start();
		$output = '';
		require $this->getTemplatePath();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * Get template path for Donor Profile component template
	 * @since 2.10.0
	 **/
	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/DonorProfiles/resources/views/donorprofile.php';
	}

	/**
	 * Enqueue assets for front-end donor profiles
	 *
	 * @return void
	 * @since 2.10.0
	 **/
	public function loadAssets() {
		wp_enqueue_script(
			'give-donor-profiles-app',
			GIVE_PLUGIN_URL . 'assets/dist/js/donor-profiles-app.js',
			[ 'wp-element', 'wp-i18n' ],
			GIVE_VERSION,
			true
		);

		wp_localize_script(
			'give-donor-profiles-app',
			'giveDonorProfileData',
			[
				'apiRoot'            => esc_url_raw( rest_url() ),
				'apiNonce'           => wp_create_nonce( 'wp_rest' ),
				'profile'            => give()->donorProfile->getProfileData(),
				'countries'          => LocationList::getCountries(),
				'states'             => LocationList::getStates( give()->donorProfile->getCountry() ),
				'id'                 => give()->donorProfile->getId(),
				'emailAccessEnabled' => give_is_setting_enabled( give_get_option( 'email_access' ) ),
				'registeredTabs' => give()->donorProfileTabs->getRegisteredIds(),
			]
		);

		wp_enqueue_style(
			'give-google-font-montserrat',
			'https://fonts.googleapis.com/css?family=Montserrat:500,500i,600,600i,700,700i&display=swap',
			[],
			null
		);

		wp_enqueue_style(
			'give-donor-profiles-app',
			GIVE_PLUGIN_URL . 'assets/dist/css/donor-profiles-app.css',
			[ 'give-google-font-montserrat' ],
			GIVE_VERSION
		);
	}
}
