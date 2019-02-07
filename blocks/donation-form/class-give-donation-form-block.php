<?php
/**
 * Give Donation Form Block Class
 *
 * @package     Give
 * @subpackage  Classes/Blocks
 * @copyright   Copyright (c) 2018, GiveWP
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
	 * Instance.
	 *
	 * @since
	 * @access private
	 * @var Give_Donation_Form_Block
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since
	 * @access public
	 * @return Give_Donation_Form_Block
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
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
	private function init() {
		add_action( 'init', array( $this, 'register_block' ), 999 );
	}

	/**
	 * Register block
	 *
	 * @since  2.1
	 * @access public
	 *
	 * @access public
	 */
	public function register_block() {
		// Bailout.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register block.
		register_block_type(
			'give/donation-form', array(
				'render_callback' => array( $this, 'render_donation_form' ),
				'attributes'      => array(
					'id'                  => array(
						'type' => 'number',
					),
					'prevId'              => array(
						'type' => 'number',
					),
					'displayStyle'        => array(
						'type'    => 'string',
						'default' => 'onpage',
					),
					'continueButtonTitle' => array(
						'type'    => 'string',
						'default' => '',
					),
					'showTitle'           => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showGoal'            => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'contentDisplay'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showContent'         => array(
						'type'    => 'string',
						'default' => 'above',
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
	public function render_donation_form( $attributes ) {
		// Bailout.
		if ( empty( $attributes['id'] ) ) {
			return '';
		}

		$parameters = array();

		$parameters['id']                    = $attributes['id'];
		$parameters['show_title']            = $attributes['showTitle'];
		$parameters['show_goal']             = $attributes['showGoal'];
		$parameters['show_content']          = ! empty( $attributes['contentDisplay'] ) ? $attributes['showContent'] : 'none';
		$parameters['display_style']         = $attributes['displayStyle'];
		$parameters['continue_button_title'] = trim( $attributes['continueButtonTitle'] );

		return give_form_shortcode( $parameters );
	}
}

Give_Donation_Form_Block::get_instance();
