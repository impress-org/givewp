<?php

namespace Give\DonorProfiles;

use Give\DonorProfiles\Profile;
use Give\DonorProfiles\Helpers as DonorProfileHelpers;
use Give\DonorProfiles\Helpers\LocationList;
use Give\Views\IframeContentView;

class App {

	protected $profile;

	public function __construct() {
		$id            = DonorProfileHelpers::getCurrentDonorId();
		$this->profile = new Profile( $id );
	}

	public function getOutput( $attributes ) {

		$url = get_site_url() . '/?give-embed=donor-profile';

		if ( isset( $attributes['accent_color'] ) ) {
			$url = $url . '&accent-color=' . urlencode( $attributes['accent_color'] );
		}

		$loader = $this->getIframeLoader( $attributes['accent_color'] );

		$iframe = sprintf(
			'<div style="position: relative; max-width: 100%%;"><iframe
				name="give-embed-donor-profile"
				%1$s
				%4$s
				data-autoScroll="%2$s"
				onload="if( \'undefined\' !== typeof Give ) { Give.initializeIframeResize(this) }"
				style="border: 0;visibility: hidden;%3$s"></iframe>%5$s</div>',
			"src=\"{$url}#/dashboard\"",
			true,
			'min-height: 776px; width: 100%; max-width: 100% !important;',
			'',
			$loader
		);

		return $iframe;
	}

	/**
	 * Get output markup for Donor Profile app
	 *
	 * @since 2.10.0
	 **@return string
	 */
	public function getIframeLoader( $accentColor ) {
		ob_start();
		$output = '';
		require $this->getLoaderTemplatePath();
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Get output markup for Donor Profile app
	 *
	 * @since 2.10.0
	 **@return string
	 */
	public function getIframeContent() {
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
	 * Get template path for Donor Profile component template
	 * @since 2.10.0
	 **/
	public function getLoaderTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/DonorProfiles/resources/views/donorprofileloader.php';
	}

	/**
	 * Enqueue assets for front-end donor profiles
	 *
	 * @since 2.10.0
	 **@return void
	 */
	public function loadAssets() {
		wp_enqueue_script(
			'give-donor-profiles-app',
			GIVE_PLUGIN_URL . 'assets/dist/js/donor-profiles-app.js',
			[ 'wp-element', 'wp-i18n' ],
			GIVE_VERSION,
			true
		);

		$recaptcha_key     = give_get_option( 'recaptcha_key' );
		$recaptcha_secret  = give_get_option( 'recaptcha_secret' );
		$recaptcha_enabled = ( give_is_setting_enabled( give_get_option( 'enable_recaptcha' ) ) ) && ! empty( $recaptcha_key ) && ! empty( $recaptcha_secret ) ? true : false;

		wp_localize_script(
			'give-donor-profiles-app',
			'giveDonorProfileData',
			[
				'apiRoot'              => esc_url_raw( rest_url() ),
				'apiNonce'             => wp_create_nonce( 'wp_rest' ),
				'profile'              => give()->donorProfile->getProfileData(),
				'countries'            => LocationList::getCountries(),
				'states'               => LocationList::getStates( give()->donorProfile->getCountry() ),
				'id'                   => give()->donorProfile->getId(),
				'emailAccessEnabled'   => give_is_setting_enabled( give_get_option( 'email_access' ) ),
				'registeredTabs'       => give()->donorProfileTabs->getRegisteredIds(),
				'loggedInWithoutDonor' => get_current_user_id() !== 0 && give()->donorProfile->getId() === null ? true : false,
				'recaptchaKey'         => $recaptcha_enabled ? $recaptcha_key : '',
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
