<?php
/**
 * Give Donation Grid Block Class
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
 * Give_Donation_form_Grid_Block Class.
 *
 * This class handles donation forms block.
 *
 * @since 2.0.2
 */
class Give_Donation_form_Grid_Block {
	/**
	 * Instance.
	 *
	 * @since
	 * @access private
	 * @var Give_Donation_form_Grid_Block
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
	 * @return Give_Donation_form_Grid_Block
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
	 *
	 * @access public
	 */
	public function register_block() {
		// Bailout.
		if( ! function_exists('register_block_type' ) ) {
			return;
		}

		// Register block.
		register_block_type( 'give/donation-form-grid', array(
			'render_callback' => array( $this, 'render_block' ),
			'attributes'      => array(
				'columns'           => array(
					'type'    => 'string',
					'default' => '4',
				),
				'showExcerpt'       => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'showGoal'          => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'showFeaturedImage' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'displayType'       => array(
					'type'    => 'string',
					'default' => 'redirect',
				),
			),
		) );
	}

	/**
	 * Block render callback
	 *
	 * @param array $attributes Block parameters.
	 *
	 * @access public
	 * @return string;
	 */
	public function render_block( $attributes ) {
		$parameters = array(
			'columns'             => absint( $attributes['columns'] ),
			'show_goal'           => $attributes['showGoal'],
			'show_excerpt'        => $attributes['showExcerpt'],
			'show_featured_image' => $attributes['showFeaturedImage'],
			'display_type'        => $attributes['displayType'],
		);

		return give_form_grid_shortcode( $parameters );
	}
}

Give_Donation_form_Grid_Block::get_instance();
