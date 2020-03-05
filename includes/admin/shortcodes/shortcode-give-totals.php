<?php
/**
 * The [give_totals] Shortcode Generator class
 *
 * @package     Give/Admin/Shortcodes
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Shortcode_Totals
 */
class Give_Shortcode_Totals extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = __( 'GiveWP Totals', 'give' );
		$this->shortcode['label'] = __( 'GiveWP Totals', 'give' );

		parent::__construct( 'give_totals' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @since 2.1
	 * @return array
	 */
	public function define_fields() {

		$category_options = array();
		$category_lists   = array();
		$categories       = get_terms( 'give_forms_category', apply_filters( 'give_forms_category_dropdown', array() ) );
		if ( give_is_setting_enabled( give_get_option( 'categories' ) ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				$category_options[ absint( $category->term_id ) ] = esc_html( $category->name );
			}

			$category_lists['type']    = 'listbox';
			$category_lists['name']    = 'cats';
			$category_lists['label']   = __( 'Select a Donation Form Category:', 'give' );
			$category_lists['tooltip'] = __( 'Select a Donation Form Category', 'give' );
			$category_lists['options'] = $category_options;
		}

		$tag_options = array();
		$tag_lists   = array();
		$tags        = get_terms( 'give_forms_tag', apply_filters( 'give_forms_tag_dropdown', array() ) );
		if ( give_is_setting_enabled( give_get_option( 'tags' ) ) && ! is_wp_error( $tags ) ) {
			$tags = get_terms( 'give_forms_tag', apply_filters( 'give_forms_tag_dropdown', array() ) );
			foreach ( $tags as $tag ) {
				$tag_options[ absint( $tag->term_id ) ] = esc_html( $tag->name );
			}

			$tag_lists['type']    = 'listbox';
			$tag_lists['name']    = 'tags';
			$tag_lists['label']   = __( 'Select a Donation Form Tag:', 'give' );
			$tag_lists['tooltip'] = __( 'Select a Donation Form Tag', 'give' );
			$tag_lists['options'] = $tag_options;
		}

		return array(
			array(
				'type' => 'container',
				'html' => sprintf(
					'<p class="give-totals-shortcode-container-message">%s</p>',
					__( 'This shortcode shows the total amount raised towards a custom goal for one or several forms regardless of whether they have goals enabled or not.', 'give' )
				),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', __( 'Shortcode Configuration', 'give' ) ),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'ids',
				'label'   => __( 'Donation Form IDs:', 'give' ),
				'tooltip' => __( 'Enter the IDs separated by commas for the donation forms you would like to combine within the totals.', 'give' ),
			),
			$category_lists,
			$tag_lists,
			array(
				'type'     => 'textbox',
				'name'     => 'total_goal',
				'label'    => __( 'Total Goal:', 'give' ),
				'tooltip'  => __( 'Enter the total goal amount that you would like to display.', 'give' ),
				'required' => array(
					'alert' => esc_html__( 'Please enter a valid total goal amount.', 'give' ),
				),
			),
			array(
				'type'      => 'textbox',
				'name'      => 'message',
				'label'     => __( 'Message:', 'give' ),
				'tooltip'   => __( 'Enter a message to display encouraging donors to support the goal.', 'give' ),
				'value'     => apply_filters( 'give_totals_message', __( 'Hey! We\'ve raised {total} of the {total_goal} we are trying to raise for this campaign!', 'give' ) ),
				'multiline' => true,
				'minWidth'  => 300,
				'minHeight' => 60,
			),
			array(
				'type'    => 'textbox',
				'name'    => 'link',
				'label'   => __( 'Link:', 'give' ),
				'tooltip' => __( 'Enter a link to the main campaign donation form.', 'give' ),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'link_text',
				'label'   => __( 'Link Text:', 'give' ),
				'tooltip' => __( 'Enter hyperlink text for the link to the main campaign donation form.', 'give' ),
				'value'   => __( 'Donate!', 'give' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'progress_bar',
				'label'   => __( 'Show Progress Bar:', 'give' ),
				'tooltip' => __( 'Select whether you would like to show a goal progress bar.', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
				'value'   => 'true',
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donation Totals Shortcode', 'give' ),
				'link' => 'http://docs.givewp.com/shortcode-donation-totals',
			),
		);
	}
}

new Give_Shortcode_Totals();
