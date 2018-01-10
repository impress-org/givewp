<?php
/**
 * The [give_total] Shortcode Generator class
 *
 * @package     Give/Admin/Shortcodes
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Shortcode_Total
 */
class Give_Shortcode_Total extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = __( 'Give Total', 'give' );
		$this->shortcode['label'] = __( 'Give Total', 'give' );

		parent::__construct( 'give_total' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @since 2.0.1
	 * @return array
	 */
	public function define_fields() {

		$categories       = get_terms( 'give_forms_category', apply_filters( 'give_forms_category_dropdown', array() ) );
		$category_options = array();
		foreach ( $categories as $category ) {
			$category_options[ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$tags        = get_terms( 'give_forms_tag', apply_filters( 'give_forms_tag_dropdown', array() ) );
		$tag_options = array();
		foreach ( $tags as $tag ) {
			$tag_options[ absint( $tag->term_id ) ] = esc_html( $tag->name );
		}

		return array(
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', esc_html__( 'Optional settings', 'give' ) ),
			),
			array(
				'type'        => 'post',
				'query_args'  => array(
					'post_type' => 'give_forms',
				),
				'name'        => 'ids',
				'label'       => esc_attr__( 'Select a Donation Form:', 'give' ),
				'tooltip'     => esc_attr__( 'Select a Donation Form', 'give' ),
				'placeholder' => '- ' . esc_attr__( 'Select a Donation Form', 'give' ) . ' -',
			),
			array(
				'type'    => 'listbox',
				'name'    => 'cats',
				'label'   => esc_attr__( 'Select a Donation Form Category:', 'give' ),
				'tooltip' => esc_attr__( 'Select a Donation Form Category', 'give' ),
				'options' => $category_options,
			),
			array(
				'type'    => 'listbox',
				'name'    => 'tags',
				'label'   => esc_attr__( 'Select a Donation Form Tag:', 'give' ),
				'tooltip' => esc_attr__( 'Select a Donation Form Tag', 'give' ),
				'options' => $tag_options,
			),
			array(
				'type'    => 'textbox',
				'name'    => 'total_goal',
				'label'   => esc_attr__( 'Total Goal:', 'give' ),
				'tooltip' => esc_attr__( 'Enter the total goal amount.', 'give' ),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'link',
				'label'   => esc_attr__( 'Link:', 'give' ),
				'tooltip' => esc_attr__( 'Link', 'give' ),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'link_text',
				'label'   => esc_attr__( 'Link Text:', 'give' ),
				'tooltip' => esc_attr__( 'Link text', 'give' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'progress_bar',
				'label'   => esc_attr__( 'Show Progress Bar:', 'give' ),
				'tooltip' => esc_attr__( 'Give total string display with Progress bar.', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),

		);
	}
}

new Give_Shortcode_Total;