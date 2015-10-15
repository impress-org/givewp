<?php
/**
 * The [give_receipt] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

defined( 'ABSPATH' ) or exit;

class Give_Shortcode_Donation_Receipt extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = __( 'Donation Receipt', 'give' );
		$this->shortcode['label'] = __( 'Donation Receipt', 'give' );

		parent::__construct( 'give_receipt' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {

		return array(
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong">%s</p>', __( 'Optional settings', 'give' ) ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'price',
				'label'   => __( 'Show Price:', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'date',
				'label'   => __( 'Show Date:', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'payment_key',
				'label'   => __( 'Show Payment Key:', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'payment_method',
				'label'   => __( 'Show Payment Method:', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'payment_id',
				'label'   => __( 'Show Payment ID:', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
		);
	}
}

new Give_Shortcode_Donation_Receipt;
