<?php

namespace Give\Onboarding\Wizard;

defined( 'ABSPATH' ) || exit;

use Give\Onboarding\Helpers\FormatList;
use Give\Onboarding\FormRepository;
use Give\Onboarding\SettingsRepositoryFactory;
use Give\Onboarding\LocaleCollection;

/**
 * Onboarding Wizard admin page class
 *
 * Responsible for setting up and rendering Onboarding Wizard page at
 * wp-admin/?page=give-onboarding-wizard
 *
 * @since 2.8.0
 */
class Page {

	/** @var string $slug Page slug used for displaying onboarding wizard */
	protected $slug = 'give-onboarding-wizard';

	/** @var FormRepository */
	protected $formRepository;

	/** @var SettingsRepository */
	protected $settingsRepository;

	/** @var LocaleCollection */
	protected $localeCollection;

	/**
	 * @param FormRepository $formRepository
	 * @param SettingsRepositoryFactory $settingsRepositoryFactory
	 */
	public function __construct(
		FormRepository $formRepository,
		SettingsRepositoryFactory $settingsRepositoryFactory,
		LocaleCollection $localeCollection
	) {
		$this->formRepository     = $formRepository;
		$this->settingsRepository = $settingsRepositoryFactory->make( 'give_onboarding' );
		$this->localeCollection   = $localeCollection;
	}

	/**
	 * Adds Onboarding Wizard as dashboard page
	 *
	 * Register Onboarding Wizard as an admin page route
	 *
	 * @since 2.8.0
	 **/
	public function add_page() {
		add_dashboard_page( '', '', 'manage_options', $this->slug, '' );
	}

	/**
	 * Conditionally renders Onboarding Wizard
	 *
	 * If the current page query matches the onboarding wizard's slug, method renders the onboarding wizard.
	 *
	 * @since 2.8.0
	 **/
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || $this->slug !== $_GET['page'] ) { // WPCS: CSRF ok, input var ok.
			return;
		} else {
			$this->render_page();
		}
	}

	/**
	 * Renders onboarding wizard markup
	 *
	 * Uses an object buffer to display the onboarding wizard template
	 *
	 * @since 2.8.0
	 **/
	public function render_page() {

		$this->formRepository->getOrMake();

		ob_start();
		include_once plugin_dir_path( __FILE__ ) . 'templates/index.php';
		exit;

	}

	/**
	 * Enqueues onboarding wizard scripts/styles
	 *
	 * Enqueues scripts/styles necessary for loading the Onboarding Wizard React app,
	 * and localizes some additional data for the app to access.
	 *
	 * @since 2.8.0
	 **/
	public function enqueue_scripts() {

		if ( empty( $_GET['page'] ) || $this->slug !== $_GET['page'] ) { // WPCS: CSRF ok, input var ok.
			return;
		}

		wp_enqueue_style(
			'give-google-font-montserrat',
			'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap',
			[],
			null
		);

		wp_enqueue_style(
			'give-google-font-open-sans',
			'https://fonts.googleapis.com/css2?family=Open+Sans:wght@600&display=swap',
			[],
			null
		);

		wp_enqueue_style(
			'give-admin-onboarding-wizard',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-onboarding-wizard.css',
			[
				'give-google-font-montserrat',
				'give-google-font-open-sans',
			],
			GIVE_VERSION
		);

		wp_enqueue_script(
			'give-admin-onboarding-wizard-app',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-onboarding-wizard.js',
			[ 'wp-element', 'wp-api', 'wp-i18n' ],
			GIVE_VERSION,
			true
		);

		wp_set_script_translations( 'give-admin-onboarding-wizard-app', 'give' );

		$formID           = $this->formRepository->getOrMake();
		$featureGoal      = get_post_meta( $formID, '_give_goal_option', true );
		$featureComments  = get_post_meta( $formID, '_give_donor_comment', true );
		$featureTerms     = get_post_meta( $formID, '_give_terms_option', true );
		$featureAnonymous = get_post_meta( $formID, '_give_anonymous_donation', true );
		$featureCompany   = get_post_meta( $formID, '_give_company_field', true );

		wp_localize_script(
			'give-admin-onboarding-wizard-app',
			'giveOnboardingWizardData',
			[
				'apiRoot'        => esc_url_raw( rest_url() ),
				'apiNonce'       => wp_create_nonce( 'wp_rest' ),
				'setupUrl'       => admin_url( 'edit.php?post_type=give_forms&page=give-setup' ),
				'formPreviewUrl' => admin_url( '?page=give-form-preview' ),
				'localeCurrency' => $this->localeCollection->pluck( 'currency_code' ),
				'currencies'     => FormatList::fromKeyValue( give_get_currencies_list() ),
				'countries'      => FormatList::fromKeyValue( give_get_country_list() ),
				'states'         => FormatList::fromKeyValue( give_get_states( 'US' ) ),
				'features'       => FormatList::fromValueKey(
					[
						'donation-goal'       => ( 'enabled' == $featureGoal ),
						'donation-comments'   => ( 'enabled' == $featureComments ),
						'terms-conditions'    => ( 'enabled' == $featureTerms ),
						'anonymous-donations' => ( 'enabled' == $featureAnonymous ),
						'company-donations'   => in_array( $featureCompany, [ 'required', 'optional' ] ), // Note: The company field has two values for enabled, "required" and "optional".
					]
				),
				'addons'         => $this->settingsRepository->get( 'addons' ) ?: [],
			]
		);

	}

}
