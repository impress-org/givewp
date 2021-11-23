<?php

namespace Give\Onboarding\Wizard;

defined('ABSPATH') || exit;

use Give\Onboarding\FormRepository;
use Give_Scripts;

/**
 * Form Preview page class
 *
 * Responsible for setting up and rendering Form Preview page at wp-admin/?page=give-form-preview
 * This URL is used as the src for an iframe which appears inside the Onboarding Wizard.
 *
 * @since 2.8.0
 */
class FormPreview
{

    /** @var string $slug Page slug used for displaying form preview */
    protected $slug = 'give-form-preview';

    /** @var FormRepository */
    protected $formRepository;

    public function __construct(FormRepository $formRepository)
    {
        $this->formRepository = $formRepository;
    }

    /**
     * Adds Form Preview as dashboard page
     *
     * Register Form Preview as an admin page route
     *
     * @since 2.8.0
     **/
    public function add_page()
    {
        add_submenu_page('', '', '', 'manage_options', $this->slug);
    }

    /**
     * Conditionally renders Form Preview markup
     *
     * If the current page query matches the form preview's slug, method renders the form preview.
     *
     * @since 2.8.0
     **/
    public function setup_form_preview()
    {
        if (empty($_GET['page']) || $this->slug !== $_GET['page']) { // WPCS: CSRF ok, input var ok.
            return;
        } else {
            $this->render_page();
        }
    }

    /**
     * Renders form preview markup
     *
     * Uses an object buffer to display the form preview template
     *
     * @since 2.8.0
     **/
    public function render_page()
    {
        $this->register_scripts();
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'templates/form-preview.php';
        exit;
    }

    /**
     * Registers form preview scripts/styles
     *
     * @since 2.8.0
     **/
    protected function register_scripts()
    {
        wp_register_style(
            'give-styles',
            (new Give_Scripts)->get_frontend_stylesheet_uri(),
            [],
            GIVE_VERSION,
            'all'
        );

        wp_register_script(
            'give',
            GIVE_PLUGIN_URL . 'assets/dist/js/give.js',
            ['jquery'],
            GIVE_VERSION
        );
    }

    /**
     * Returns the ID of the form used for the form preview
     *
     * @since 2.8.0
     **/
    protected function get_preview_form_id()
    {
        return $this->formRepository->getOrMake();
    }

}
