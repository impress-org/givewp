<?php
/**
 * Give Donor Wall Block Class
 *
 * @package     Give
 * @subpackage  Classes/Blocks
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donor_Wall_Block Class.
 *
 * This class handles donation forms block.
 *
 * @since 2.3.0
 */
class Give_Donor_Wall_Block {
	/**
	 * Instance.
	 *
	 * @since
	 * @access private
	 * @var Give_Donor_Wall_Block
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
	 * @return Give_Donor_Wall_Block
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
	 * @since  2.3.0
	 * @access private
	 */
	private function init() {
		add_action( 'init', array( $this, 'register_block' ), 999 );
	}

	/**
	 * Register block
	 *
	 * @access public
	 */
	public function register_block() {
		// Bailout.
		if ( ! function_exists('register_block_type' ) ) {
			return;
		}

		// Register block.
		register_block_type( 'give/donor-wall', array(
			'render_callback' => array( $this, 'render_block' ),
			'attributes'      => array(
				'columns' => array(
					'type'    => 'string',
					'default' => '2',
				),
				'showAvatar' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showName' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showTotal' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showDate' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showComments' => array(
					'type'    => 'boolean',
					'default' => true,
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
			'columns'       => absint( $attributes['columns'] ),
			'show_avatar'   => $attributes['showAvatar'],
			'show_name'     => $attributes['showName'],
			'show_total'    => $attributes['showTotal'],
			'show_time'     => $attributes['showDate'],
			'show_comments' => $attributes['showComments'],
		);

		$donor_wall = new Give_Donor_Wall();

		return $donor_wall->render_shortcode( $parameters );
	}
}

Give_Donor_Wall_Block::get_instance();
