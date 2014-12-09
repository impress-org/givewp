<?php
/**
 * Metabox Functions
 *
 * @package     Give
 * @subpackage  Admin/Downloads
 * @copyright   Copyright (c) 2014, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_filter( 'cmb2_meta_boxes', 'cmb2_sample_metaboxes' );

/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 *
 * @return array
 */
function cmb2_sample_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_give_';

	/**
	 * Repeatable Field Groups
	 */
	$meta_boxes['form_field_options'] = apply_filters( 'give_form_field_options', array(
			'id'           => 'form_field_options',
			'title'        => __( 'Create a New Donation Form', 'give' ),
			'object_types' => array( 'give_forms', ),
			'context'      => 'normal',
			'priority'     => 'high', //Show above Content WYSIWYG
			'fields'       => array(
				//Donation levels: Header
				array(
					'id'   => $prefix . 'levels_header',
					'type' => 'levels_repeater_header',
				),
				//Donation Levels: Repeatable CMB2 Group
				array(
					'id'      => $prefix . 'donation_levels',
					'type'    => 'group',
					'options' => array(
						'add_button'    => __( 'Add Level', 'give' ),
						'remove_button' => __( 'Remove Level', 'give' ),
						'sortable'      => true, // beta
					),
					// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
					'fields'  => array(
						array(
							'name' => __( 'Amount', 'give' ),
							'id'   => $prefix . 'amount',
							'type' => 'text_money',
						),
						array(
							'name'       => __( 'Text', 'give' ),
							'id'         => $prefix . 'text',
							'type'       => 'text',
							'attributes' => array(
								'placeholder' => 'Donation Level',
								'rows'        => 3,
								'required'    => 'required',
							),
						),
						array(
							'name'    => __( 'Default', 'give' ),
							'id'      => $prefix . 'default',
							'type'    => 'radio_inline',
							'options' => array(
								'default' => __( 'Default', 'cmb' ),
							),
						),
					),
				),
				//Display Style
				array(
					'name'        => __( 'Display Style', 'give' ),
					'description' => __( 'Set how the donations levels will display on the form.', 'give' ),
					'id'          => $prefix . 'display_style',
					'type'        => 'radio_inline',
					'default'     => 'buttons',
					'options'     => array(
						'buttons'  => __( 'Buttons', 'cmb' ),
						'radios'   => __( 'Radios', 'cmb' ),
						'dropdown' => __( 'Dropdown', 'cmb' ),
					),
				),
				//Custom Amount
				array(
					'name'        => __( 'Custom Amount', 'give' ),
					'description' => __( 'Do you want the user to be able to input their own donation amount?', 'give' ),
					'id'          => $prefix . 'custom_amount',
					'type'        => 'radio_inline',
					'default'     => 'no',
					'options'     => array(
						'yes' => __( 'Yes', 'cmb' ),
						'no'  => __( 'No', 'cmb' ),
					),
				),
			),
		)
	);

	return $meta_boxes;

}

/**
 * Repeatable Levels Custom Field
 */
add_action( 'cmb2_render_levels_repeater_header', 'give_cmb_render_levels_repeater_header', 10 );
function give_cmb_render_levels_repeater_header() {
	?>

	<div class="table-container">
		<div class="table-row">
			<div class="table-cell col-amount"><?php _e( 'Amount', 'give' ); ?></div>
			<div class="table-cell col-text"><?php _e( 'Text', 'give' ); ?></div>
			<div class="table-cell col-default"><?php _e( 'Default', 'give' ); ?></div>
			<div class="table-cell col-sort"><?php _e( 'Sort', 'give' ); ?></div>
		</div>
	</div>

<?php }