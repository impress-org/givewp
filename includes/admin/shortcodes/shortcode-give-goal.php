<?php
/**
 * The [give_goal] Shortcode Generator class
 *
 * @package     Give/Admin/Shortcodes
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Shortcode_Donation_Form_Goal
 */
class Give_Shortcode_Donation_Form_Goal extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Donation Form Goal', 'give' );
		$this->shortcode['label'] = esc_html__( 'Donation Form Goal', 'give' );

		parent::__construct( 'give_goal' );
	}

	/**
	 * Define the shortcode attribute fields
     *
     * @unreleased Replace "new form" with "new campaign form" link
	 *
	 * @return array
	 */
	public function define_fields() {

		$create_form_link = sprintf(
			/* translators: %s: create new form URL */
            __('<a href="%s">Create</a> a new Campaign Donation Form.', 'give'),
            admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign')
		);

		return [
			[
				'type'        => 'post',
				'query_args'  => [
					'post_type'  => 'give_forms',
					'meta_key'   => '_give_goal_option',
					'meta_value' => 'enabled',
				],
				'name'        => 'id',
				'tooltip'     => esc_attr__( 'Select a Donation Form', 'give' ),
                'placeholder' => '- ' . esc_attr__('Select a Campaign Donation Form', 'give') . ' -',
				'required'    => [
                    'alert' => esc_html__('You must first select a Campaign Form!', 'give'),
                    'error' => sprintf('<p class="strong">%s</p><p class="no-margin">%s</p>',
                        esc_html__('No campaign forms found.', 'give'), $create_form_link),
				],
			],
			[
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', esc_html__( 'Optional settings', 'give' ) ),
			],
			[
				'type'    => 'listbox',
				'name'    => 'show_text',
				'label'   => esc_attr__( 'Show Text:', 'give' ),
				'tooltip' => esc_attr__( 'This text displays the amount of revenue raised compared to the goal.', 'give' ),
				'options' => [
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				],
			],
			[
				'type'    => 'listbox',
				'name'    => 'show_bar',
				'label'   => esc_attr__( 'Show Progress Bar:', 'give' ),
				'tooltip' => esc_attr__( 'Do you want to display the goal\'s progress bar?', 'give' ),
				'options' => [
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				],
			],
			[
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Goal Shortcode', 'give' ),
				'link' => 'http://docs.givewp.com/shortcode-give-goal',
			],
		];
	}
}

new Give_Shortcode_Donation_Form_Goal();
