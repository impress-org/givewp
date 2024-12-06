<?php

namespace Give\DonorDashboards;

use Give\DonorDashboards\Helpers\LocationList;
use Give\Helpers\EnqueueScript;

/**
 * Class App
 * @package Give\DonorDashboards
 *
 * @since 2.10.2
 */
class App
{
    /**
     * @var Profile
     */
    protected $profile;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->profile = new Profile();
    }

    /**
     * @since 3.6.0 Escape attributes
     *
     * @param array $attributes
     *
     * @return string
     */
    public function getOutput($attributes)
    {
        $url = get_site_url() . '/?give-embed=donor-dashboard';

        $queryArgs = [];

        if (isset($attributes['accent_color'])) {
            $queryArgs['accent-color'] = urlencode(esc_attr($attributes['accent_color']));
        }

        if (isset($_GET['give_nl'])) {
            $queryArgs['give_nl'] = urlencode(give_clean($_GET['give_nl']));
        }

        if (isset($_GET['_give_hash'])) {
            $queryArgs['_give_hash'] = urlencode(give_clean($_GET['_give_hash']));
        }

        if (isset($_GET['action'])) {
            $queryArgs['action'] = urlencode(give_clean($_GET['action']));
        }

        $url = esc_url(add_query_arg($queryArgs, $url));

        $loader = $this->getIframeLoader(esc_attr($attributes['accent_color']));

        return sprintf(
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
    }

    /**
     * Get output markup for Donor Dashboard app
     *
     * @since 2.10.0
     *
     * @param string $accentColor
     *
     * @return string
     */
    public function getIframeLoader($accentColor)
    {
        ob_start();

        require $this->getLoaderTemplatePath();

        return ob_get_clean();
    }

    /**
     * Get output markup for Donor Dashboard app
     *
     * @since 2.10.0
     * @return string
     */
    public function getIframeContent()
    {
        ob_start();

        require $this->getTemplatePath();

        return ob_get_clean();
    }

    /**
     * Get template path for Donor Dashboard component template
     * @since 2.10.0
     **/
    public function getTemplatePath()
    {
        return GIVE_PLUGIN_DIR . '/src/DonorDashboards/resources/views/donordashboard.php';
    }

    /**
     * Get template path for Donor Dashboard component template
     * @since 2.10.0
     **/
    public function getLoaderTemplatePath()
    {
        return GIVE_PLUGIN_DIR . '/src/DonorDashboards/resources/views/donordashboardloader.php';
    }

    /**
     * Enqueue assets for front-end donor dashboards
     *
     * @since 3.19.0 Add action to allow enqueueing additional assets.
     * @since      2.11.0 Set script translations.
     * @since 2.10.0
     *
     * @return void
     */
    public function loadAssets()
    {
        // Load assets only if rendering donor dashboard.
        if (!isset($_GET['give-embed']) || 'donor-dashboard' !== $_GET['give-embed']) {
            return;
        }

        $recaptcha_key = give_get_option('recaptcha_key');
        $recaptcha_secret = give_get_option('recaptcha_secret');
        $recaptcha_enabled = (give_is_setting_enabled(give_get_option('enable_recaptcha'))) &&
            !empty($recaptcha_key) && !empty($recaptcha_secret);

        $data = [
            'apiRoot' => esc_url_raw(rest_url()),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'profile' => give()->donorDashboard->getProfileData(),
            'countries' => LocationList::getCountries(),
            'states' => LocationList::getStates(give()->donorDashboard->getCountry()),
            'id' => give()->donorDashboard->getId(),
            'emailAccessEnabled' => give_is_setting_enabled(give_get_option('email_access')),
            'loginEnabled' => $this->loginEnabled(),
            'registeredTabs' => give()->donorDashboardTabs->getRegisteredIds(),
            'loggedInWithoutDonor' => get_current_user_id() !== 0 && give()->donorDashboard->getId() === null,
            'recaptchaKey' => $recaptcha_enabled ? $recaptcha_key : '',
        ];

        EnqueueScript::make(
            'give-donor-dashboards-app',
            'assets/dist/js/donor-dashboards-app.js'
        )
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('giveDonorDashboardData', $data)
            ->enqueue();

        wp_enqueue_style(
            'give-google-font-montserrat',
            'https://fonts.googleapis.com/css?family=Montserrat:500,500i,600,600i,700,700i&display=swap',
            [],
            null
        );

        do_action('give_donor_dashboard_enqueue_assets');
    }

    /**
     * Determine if the login should be enabled.
     *
     * @since 2.15.0
     *
     * @return bool
     */
    protected function loginEnabled()
    {
        // We need to get all the form IDs.
        $formIds = get_posts(
            [
                'fields' => 'ids',
                'numberposts' => -1,
                'post_status' => 'publish',
                'post_type' => 'give_forms',
            ]
        );

        // By default, the login is disabled.
        $loginEnabled = false;
        foreach ($formIds as $formId) {
            if (give_show_login_register_option($formId) !== 'none') {
                // Once there is a single form that it is enabled, we can bail out
                // early since the login needs to be enabled.
                $loginEnabled = true;
                break;
            }
        }

        return $loginEnabled;
    }
}
