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

		if ( is_admin() ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		} else {
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
	 * Load editor scripts
	 *
	 * Enqueue required scripts and styles for editor block
	 *
	 * @access public
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		global $current_user;

		// Localize vars from PHP.
		wp_localize_script( 'give-blocks-js', 'give_blocks_vars', array(
			'key'   => Give()->api->get_user_public_key( $current_user->ID ),
			'token' => Give()->api->get_token( $current_user->ID ),
		));

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
		'reveal' === $attributes['displayStyle'] && // show continue button if display_style is "reveal"
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
		$form    = get_post( $form_id );

		// Response data array
		$response = array(
			'title' => $form->post_title,
		);

		return $response;
	}
}
