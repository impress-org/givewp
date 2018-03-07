<?php
/**
 * Give Donation Form Block Class
 *
 * @package     Give
 * @subpackage  Classes/Blocks
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donation_Form_Block Class.
 *
 * This class handles donation forms block.
 *
 * @since 2.0.2
 */
class Give_Donation_Form_Block {

	/**
	 * Class Constructor
	 *
	 * Set up the Give Donation Form Block class.
	 *
	 * @since  2.0.2
	 * @access public
	 */
	public function __construct() {

		add_action( 'rest_api_init', array( $this, 'register_rest_api' ) );

		if ( !is_admin() ) {
			if ( function_exists( 'register_block_type' ) ) {
				register_block_type( 'give/donation-form', array(
					'render_callback' => array( $this, 'render_donation_form' ),
					'attributes'      => array(
						'id'                  => array(
							'type' => 'number',
						),
						'displayStyle'        => array(
							'type' => 'string',
						),
						'continueButtonTitle' => array(
							'type' => 'string',
						),
						'showTitle'           => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'showGoal'            => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'showContent'         => array(
							'type'    => 'string',
							'default' => 'none',
						),
					),
				) );
			}
		}
	}

	/**
	 * Block render callback
	 *
	 * @param array $attributes Block parameters.
	 * @access public
	 * @return string;
	 */
	public function render_donation_form( $attributes ) {

		// Bailout.
		if ( empty( $attributes['id'] ) ) {
			return;
		}

		$parameters = array();

		$parameters[] = 'id="' . $attributes['id'] . '"';
		$parameters[] = 'show_title="' . var_export( $attributes['showTitle'], 1 ) . '"';
		$parameters[] = 'show_goal="' . var_export( $attributes['showGoal'], 1 ) . '"';
		$parameters[] = 'show_content="' . $attributes['showContent'] . '"';
		$parameters[] = 'display_style="' . $attributes['displayStyle'] . '"';
		'reveal' === $attributes['displayStyle'] && ! empty( $attributes['continueButtonTitle'] ) && // show continue button if display_style is "reveal"
		$parameters[] = 'continue_button_title="' . trim( $attributes['continueButtonTitle'] ) . '"';

		return do_shortcode( '[give_form ' . join( ' ', $parameters ) . ' ]' );
	}

	/**
	* Register rest route to fetch form data
	* @TODO: This is a temporary solution. Next step would be to find a solution that is limited to the editor.
	* @access public
	* @return void
	*/
	public function register_rest_api() {
		register_rest_route( 'give-api/v1', '/form/(?P<id>\d+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_forms_data' ),
		) );
	}

	/**
	* Rest fetch form data callback
	* @param $request
	* @access public
	* @return array|mixed|object
	*/
	public function get_forms_data( $request ) {
		$parameters = $request->get_params();

		// Bailout
		if ( ! isset( $parameters['id'] ) || empty( $parameters['id'] ) ) {
			return array( 'error' => 'no_parameter_given' );
		}

		$form_id = $parameters['id'];

		$parameters = array();

		$parameters[] = 'id="' . $form_id . '"';
		$parameters[] = 'show_title="' . sanitize_text_field( $_GET['show_title'] ) . '"';
		$parameters[] = 'show_goal="' . sanitize_text_field( $_GET['show_goal'] ) . '"';
		$parameters[] = 'show_content="' . sanitize_text_field( $_GET['show_content'] ) . '"';
		$parameters[] = 'display_style="' . sanitize_text_field( $_GET['display_style'] ) . '"';
		'reveal' === $_GET['display_style'] && ! empty( $_GET['continue_button_title'] ) &&
		$parameters[] = 'continue_button_title="' . sanitize_text_field( $_GET['continue_button_title'] ) . '"';

		// Response data array
		$response = array(
			'html' => do_shortcode( '[give_form ' . join( ' ', $parameters ) . ' ]' ),
		);

		return $response;
	}
}
