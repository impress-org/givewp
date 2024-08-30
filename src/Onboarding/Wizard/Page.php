<?php

namespace Give\Onboarding\Wizard;

defined('ABSPATH') || exit;

use Give\DonationForms\V2\DonationFormsAdminPage;
use Give\Helpers\EnqueueScript;
use Give\Onboarding\FormRepository;
use Give\Onboarding\Helpers\FormatList;
use Give\Onboarding\Helpers\LocationList;
use Give\Onboarding\LocaleCollection;
use Give\Onboarding\SettingsRepository;
use Give\Onboarding\SettingsRepositoryFactory;
use Give\Onboarding\Setup\Page as SetupPage;

/**
 * Onboarding Wizard admin page class
 *
 * Responsible for setting up and rendering Onboarding Wizard page at
 * wp-admin/?page=give-onboarding-wizard
 *
 * @since 2.8.0
 */
class Page
{

    /** @var string $slug Page slug used for displaying onboarding wizard */
    protected $slug = 'give-onboarding-wizard';

    /** @var FormRepository */
    protected $formRepository;

    /** @var SettingsRepository */
    protected $settingsRepository;

    /** @var SettingsRepository */
    protected $onboardingSettingsRepository;

    /** @var LocaleCollection */
    protected $localeCollection;

    /**
     * @param FormRepository $formRepository
     * @param SettingsRepositoryFactory $settingsRepositoryFactory
     * @param LocaleCollection $localeCollection
     */
    public function __construct(
        FormRepository $formRepository,
        SettingsRepositoryFactory $settingsRepositoryFactory,
        LocaleCollection $localeCollection
    ) {
        $this->formRepository = $formRepository;
        $this->settingsRepository = $settingsRepositoryFactory->make('give_settings');
        $this->onboardingSettingsRepository = $settingsRepositoryFactory->make('give_onboarding');
        $this->localeCollection = $localeCollection;
    }

    /**
     * Adds Onboarding Wizard as dashboard page
     *
     * Register Onboarding Wizard as an admin page route
     *
     * @since 2.8.0
     * @since 3.14.0 change capability to manage_give_settings
     **/
    public function add_page()
    {
        add_submenu_page('', '', '', 'manage_give_settings', $this->slug);
    }

    /**
     * Conditionally renders Onboarding Wizard
     *
     * If the current page query matches the onboarding wizard's slug, method renders the onboarding wizard.
     *
     * @since 2.8.0
     * @since 3.14.0 add user capability check
     **/
    public function setup_wizard()
    {
        if (empty($_GET['page']) || $this->slug !== $_GET['page'] || ! current_user_can('manage_give_settings')) { // WPCS: CSRF ok, input var ok.
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
    public function render_page()
    {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'templates/index.php';
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
    public function enqueue_scripts()
    {
        global $current_user;

        if (empty($_GET['page']) || $this->slug !== $_GET['page']) { // WPCS: CSRF ok, input var ok.
            return;
        }

        wp_enqueue_style(
            'give-google-font-montserrat',
            'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap',
            [],
            null
        );

        wp_enqueue_style('givewp-admin-fonts');

        $formID = $this->formRepository->getDefaultFormID();
        $formPreviewUrl = home_url('/?givewp-route=donation-form-view&form-id=');
        $featureGoal = get_post_meta($formID, '_give_goal_option', true);
        $featureComments = get_post_meta($formID, '_give_donor_comment', true);
        $featureTerms = get_post_meta($formID, '_give_terms_option', true);
        $offlineDonations = get_post_meta($formID, '_give_customize_offline_donations', true);
        $featureAnonymous = get_post_meta($formID, '_give_anonymous_donation', true);
        $featureCompany = get_post_meta($formID, '_give_company_field', true);

        $currency = $this->settingsRepository->get('currency') ?: 'USD';
        $baseCountry = $this->settingsRepository->get('base_country') ?: 'US';
        $baseState = $this->settingsRepository->get('base_state') ?: '';
        $data = [
            'apiRoot' => esc_url_raw(rest_url()),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'setupUrl' => SetupPage::getSetupPageEnabledOrDisabled() === SetupPage::ENABLED ?
                admin_url('edit.php?post_type=give_forms&page=give-setup') :
                DonationFormsAdminPage::getUrl(),
            'formPreviewUrl' => $formPreviewUrl,
            'localeCurrency' => $this->localeCollection->pluck('currency_code'),
            'currencies' => FormatList::fromKeyValue(give_get_currencies_list()),
            'currencySelected' => $currency,
            'countries' => LocationList::getCountries(),
            'countrySelected' => $baseCountry,
            'states' => LocationList::getStates($baseCountry),
            'stateSelected' => $baseState,
            'features' => FormatList::fromValueKey(
                [
                    'donation-goal' => ('enabled' === $featureGoal),
                    'donation-comments' => ('enabled' === $featureComments),
                    'terms-conditions' => ('enabled' === $featureTerms),
                    'offline-donations' => ('enabled' === $offlineDonations),
                    'anonymous-donations' => ('enabled' === $featureAnonymous),
                    'company-donations' => in_array($featureCompany, ['required', 'optional']),
                    // Note: The company field has two values for enabled, "required" and "optional".
                ]
            ),
            'causeTypes' => FormatList::fromKeyValue(
                include GIVE_PLUGIN_DIR . 'src/Onboarding/Config/CauseTypes.php'
            ),
            'adminEmail' => $current_user->user_email,
            'adminFirstName' => $current_user->first_name,
            'adminLastName' => $current_user->last_name,
            'adminUserID' => $current_user->ID,
            'websiteUrl' => get_bloginfo('url'),
            'websiteName' => get_bloginfo('sitename'),
            'addons' => $this->onboardingSettingsRepository->get('addons') ?: [],
        ];

        EnqueueScript::make(
            'give-admin-onboarding-wizard-app',
            'assets/dist/js/admin-onboarding-wizard.js'
        )->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('giveOnboardingWizardData', $data)
            ->enqueue();
    }

    public function redirect()
    {
        // Bail if no activation redirect
        if (!\Give_Cache::get('_give_activation_redirect', true) || wp_doing_ajax()) {
            return;
        }

        // Delete the redirect transient
        \Give_Cache::delete(\Give_Cache::get_key('_give_activation_redirect'));

        // Bail if activating from network, or bulk
        if (is_network_admin() || isset($_GET['activate-multi'])) {
            return;
        }

        $redirect = add_query_arg('page', 'give-onboarding-wizard', admin_url());

        $upgrade = get_option('give_version_upgraded_from');

        if (!$upgrade) {
            // First time install
            wp_safe_redirect($redirect);
            exit;
        }
    }
}

register_meta('user', 'marketing_optin', [
    'type' => 'string',
    'show_in_rest' => true,
    'single' => true,
]);
