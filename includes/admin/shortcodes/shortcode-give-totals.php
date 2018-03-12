<?php
/**
 * The [give_totals] Shortcode Generator class
 *
 * @package     Give/Admin/Shortcodes
 * @copyright   Copyright (c) 2016, WordImpress
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

		$this->shortcode['title'] = __( 'Give Totals', 'give' );
		$this->shortcode['label'] = __( 'Give Totals', 'give' );

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
				'html' => sprintf( '<p class="give-totals-shortcode-container-message">%s</p>',
					 __( 'This shortcode shows the total amount raised towards a custom goal for one or several forms regardless of whether they have goals enabled or not.', 'give' )
				),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', __( 'Shortcode Configuration', 'give' ) ),
			),
			array(
				'type'        => 'post',
				'query_args'  => array(
					'post_type' => 'give_forms',
				),
				'name'        => 'ids',
				'label'       => __( 'Select a Donation Form:', 'give' ),
				'tooltip'     => __( 'Select a Donation Form', 'give' ),
				'placeholder' => '- ' . __( 'Select a Donation Form', 'give' ) . ' -',
			),
			$category_lists,
			$tag_lists,
			array(
				'type'    => 'textbox',
				'name'    => 'total_goal',
				'label'   => __( 'Total Goal:', 'give' ),
				'tooltip' => __( 'Enter the total goal amount.', 'give' ),
			),
			array(
				'type'      => 'textbox',
				'name'      => 'message',
				'label'     => __( 'Message:', 'give' ),
				'tooltip'   => __( 'Enter the message.', 'give' ),
				'value'     => apply_filters( 'give_totals_message', __( 'Hey! We\'ve raised {total} of the {total_goal} we are trying to raise for this campaign!', 'give' ) ),
				'multiline' => true,
				'minWidth'  => 300,
				'minHeight' => 60,
			),
			array(
				'type'    => 'textbox',
				'name'    => 'link',
				'label'   => __( 'Link:', 'give' ),
				'tooltip' => __( 'Enter a link of campaign.', 'give' ),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'link_text',
				'label'   => __( 'Link Text:', 'give' ),
				'tooltip' => __( 'Enter a text for the Link.', 'give' ),
				'value'   => __( 'Donate!', 'give' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'progress_bar',
				'label'   => __( 'Show Progress Bar:', 'give' ),
				'tooltip' => __( 'Choose Option to show Progress Bar or not with a message.', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
				'value'   => 'true',
			),

		);
	}
}

new Give_Shortcode_Totals;