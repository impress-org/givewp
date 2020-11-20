<?php

namespace Give\DonorProfiles;

use \Give_Donor as Donor;

class Model {

	// Settings
	protected $enabled;

	// Internal variables
	protected $tabs;

	/**
	 * Constructs and sets up setting variables for a new Donor Profile model
	 *
	 * @param array $args Arguments for new Donor Profile, including 'enabled'
	 * @since 2.10.0
	 **/
	public function __construct() {
		$this->enabled = [];
		$this->tabs    = [];
	}

	/**
	 * Get output markup for Multi-Form Goal
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
	 * Get enabled tabs for Donor Profile
	 *
	 * @return string
	 * @since 2.10.0
	 **/
	public function getEnabled() {
		return $this->enabled;
	}

	/**
	 * Get template path for Donor Profile component template
	 * @since 2.9.0
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
				'apiRoot'  => esc_url_raw( rest_url() ),
				'apiNonce' => wp_create_nonce( 'wp_rest' ),
				'profile'  => $this->getProfile(),
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

	/**
	 * Return array of donor profile data
	 *
	 * @return void
	 * @since 2.10.0
	 **/
	public function getProfile() {

		$donorId = get_current_user_id();
		$donor   = new Donor( $donorId );

		$titlePrefix = Give()->donor_meta->get_meta( $donorId, '_give_donor_title_prefix', true );

		return [
			'name'              => give_get_donor_name_with_title_prefixes( $titlePrefix, $donor->name ),
			'emails'            => $donor->emails,
			'sinceLastDonation' => human_time_diff( strtotime( $donor->get_last_donation_date() ) ),
			'avatarUrl'         => give_validate_gravatar( $donor->email ) ? get_avatar_url( $donor->email, 140 ) : null,
			'sinceCreated'      => human_time_diff( strtotime( $donor->date_created ) ),
			'company'           => $donor->get_company_name(),
			'initials'          => $donor->get_donor_initals(),
			'titlePrefix'       => Give()->donor_meta->get_meta( $donorId, '_give_donor_title_prefix', true ),
			'addresses'         => $donor->address,
		];
	}
}
