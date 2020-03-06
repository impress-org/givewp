<?php
/**
 * The [give_receipt] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Shortcode_Donation_Receipt
 */
class Give_Shortcode_Donation_Receipt extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Donation Receipt', 'give' );
		$this->shortcode['label'] = esc_html__( 'Donation Receipt', 'give' );

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
				'html' => sprintf( '<p class="strong">%s</p>', esc_html__( 'Optional settings', 'give' ) ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'price',
				'label'       => esc_html__( 'Show Donation Amount:', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'donor',
				'label'       => esc_html__( 'Show Donor Name:', 'give' ),
				'options'     => array(
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'date',
				'label'       => esc_html__( 'Show Date:', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'payment_method',
				'label'       => esc_html__( 'Show Payment Method:', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'payment_id',
				'label'       => esc_html__( 'Show Payment ID:', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'company_name',
				'label'       => esc_html__( 'Company Name:', 'give' ),
				'options'     => array(
					'true' => esc_html__( 'Show', 'give' ),
				),
				'placeholder' => esc_html__( 'Hide', 'give' ),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donation Receipt Shortcode', 'give' ),
				'link' => 'http://docs.givewp.com/shortcode-donation-receipt',
			),
		);
	}
}

new Give_Shortcode_Donation_Receipt();
