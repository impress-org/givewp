<?php
/**
 * Give Donation Form Block Class
 *
 * @since       2.0.2
 * @subpackage  Classes/Blocks
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @package     Give
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Give_Donation_Form_Block Class.
 *
 * This class handles donation forms block.
 *
 * @since 2.0.2
 */
class Give_Donation_Form_Block
{
    /**
     * Instance.
     *
     * @since
     * @access private
     * @var Give_Donation_Form_Block
     */
    private static $instance;

    /**
     * Singleton pattern.
     *
     * @since
     * @access private
     */
    private function __construct()
    {
    }


    /**
     * Get instance.
     *
     * @since
     * @access public
     * @return Give_Donation_Form_Block
     */
    public static function get_instance()
    {
        if (null === static::$instance) {
            self::$instance = new static();

            self::$instance->init();
        }

        return self::$instance;
    }

    /**
     * Class Constructor
     *
     * Set up the Give Donation Grid Block class.
     *
     * @since  2.0.2
     * @access private
     */
    private function init()
    {
        add_action('init', array($this, 'register_block'), 999);
        add_action('wp_ajax_give_block_donation_form_search_results', array($this, 'block_donation_form_search_results')
        );
        add_filter('rest_prepare_give_forms', array($this, 'addExtraDataToResponse'), 10, 2);
    }

    /**
     * Add extra data to response.
     *
     * @since 2.7.0
     * @param WP_REST_Response $response
     * @param WP_Post $form
     *
     * @return WP_REST_Response
     */
    public function addExtraDataToResponse($response, $form)
    {
        // Return extra data only if query in edit context.
        if (empty($_REQUEST['context']) || $_REQUEST['context'] !== 'edit') {
            return $response;
        }

        $data = $response->get_data();
        $data['formTemplate'] = Give()->form_meta->get_meta($form->ID, '_give_form_template', true);
        $data['isLegacyForm'] = !Give()->form_meta->get_meta($form->ID, 'formBuilderSettings', true);
        $response->set_data($data);

        return $response;
    }

    /**
     * Register block
     *
     * @since  2.1
     * @access public
     *
     * @access public
     */
    public function register_block()
    {
        // Bailout.
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register block.
        register_block_type(
            'give/donation-form',
            array(
                'render_callback' => array($this, 'render_donation_form'),
                'attributes' => array(
                    'id' => array(
                        'type' => 'number',
                    ),
                    'prevId' => array(
                        'type' => 'number',
                    ),
                    'displayStyle' => array(
                        'type' => 'string',
                        'default' => 'onpage',
                    ),
                    'continueButtonTitle' => array(
                        'type' => 'string',
                        'default' => __('Donate now', 'give')
                    ),
                    'showTitle' => array(
                        'type' => 'boolean',
                        'default' => true,
                    ),
                    'showGoal' => array(
                        'type' => 'boolean',
                        'default' => true,
                    ),
                    'contentDisplay' => array(
                        'type' => 'boolean',
                        'default' => true,
                    ),
                    'showContent' => array(
                        'type' => 'string',
                        'default' => 'above',
                    ),
                    'blockId' => array(
                        'type' => 'string',
                    ),
                ),
            )
        );
    }

    /**
     * Block render callback
     *
     * @param array $attributes Block parameters.
     *
     * @access public
     * @return string;
     */
    public function render_donation_form($attributes)
    {
        // Bailout.
        if (empty($attributes['id'])) {
            return '';
        }
        $parameters = array();

        $parameters['id'] = absint($attributes['id']);
        $parameters['show_title'] = $attributes['showTitle'];
        $parameters['show_goal'] = $attributes['showGoal'];
        $parameters['show_content'] = !empty($attributes['contentDisplay']) ? $attributes['showContent'] : 'none';
        $parameters['display_style'] = $attributes['displayStyle'];
        $parameters['continue_button_title'] = trim($attributes['continueButtonTitle']);

        _give_redirect_form_id($parameters['id']);

        return give_form_shortcode($parameters);
    }

    /**
     * This function is used to fetch donation forms based on the chosen search in the donation form block.
     *
     * @since  2.5.3
     * @access public
     *
     * @return void
     */
    public function block_donation_form_search_results()
    {
        // Define variables.
        $result = array();
        $post_data = give_clean($_POST);
        $search_keyword = !empty($post_data['search']) ? $post_data['search'] : '';

        // Setup the arguments to fetch the donation forms.
        $forms_query = new Give_Forms_Query(
            array(
                's' => $search_keyword,
                'number' => 30,
                'post_status' => 'publish',
            )
        );

        // Fetch the donation forms.
        $forms = $forms_query->get_forms();

        // Loop through each donation form.
        foreach ($forms as $form) {
            $result[] = array(
                'id' => $form->ID,
                'name' => $form->post_title,
            );
        }

        echo wp_json_encode($result);
        give_die();
    }
}

Give_Donation_Form_Block::get_instance();
