<?php
/**
 * Metabox Functions
 *
 * @package     Give
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'cmb2_init', 'give_single_forms_cmb2_metaboxes', 6 );

/**
 * Define the metabox and field configurations.
 */
function give_single_forms_cmb2_metaboxes() {

	$post_id               = give_get_admin_post_id();
	$price                 = give_get_form_price( $post_id );
	$custom_amount_minimum = give_get_form_minimum_price( $post_id );
	$goal                  = give_get_form_goal( $post_id );
	$variable_pricing      = give_has_variable_prices( $post_id );
	$prices                = give_get_variable_prices( $post_id );

	//No empty prices - min. 1.00 for new forms
	if ( empty( $price ) && is_null( $post_id ) ) {
		$price = esc_attr( give_format_amount( '1.00' ) );
	}

	//Min. $1.00 for new forms
	if ( empty( $custom_amount_minimum ) ) {
		$custom_amount_minimum = esc_attr( give_format_amount( '1.00' ) );
	}

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_give_';

	// Init metaboxes array.
	$meta_boxes = array();

	/**
	 * Repeatable Field Groups
	 */
	$meta_boxes['form_field_options'] = apply_filters( 'give_forms_field_options', array(
		'id'           => 'form_field_options',
		'title'        => esc_html__( 'Donation Options', 'give' ),
		'object_types' => array( 'give_forms' ),
		'context'      => 'normal',
		'priority'     => 'high', //Show above Content WYSIWYG
		'fields'       => apply_filters( 'give_forms_donation_form_metabox_fields', array(
				//Donation Option
				array(
					'name'        => esc_html__( 'Donation Option', 'give' ),
					'description' => esc_html__( 'Would you like this form to have one set donation price or multiple levels (for example, $10, $20, $50)?', 'give' ),
					'id'          => $prefix . 'price_option',
					'type'        => 'radio_inline',
					'default'     => 'set',
					'options'     => apply_filters( 'give_forms_price_options', array(
						'set'   => esc_html__( 'Set Donation', 'give' ),
						'multi' => esc_html__( 'Multi-level Donation', 'give' ),
					) ),
				),
				array(
					'name'         => esc_html__( 'Set Donation', 'give' ),
					'description'  => esc_html__( 'This is the set donation amount for this form. If you have a "Custom Amount Minimum" set, make sure it is less than this amount.', 'give' ),
					'id'           => $prefix . 'set_price',
					'type'         => 'text_small',
					'row_classes'  => 'give-subfield',
					'render_row_cb' 	=> 'give_cmb_amount_field_render_row_cb',
					'sanitization_cb'   => 'give_sanitize_price_field_value',
                    'attributes'   => array(
						'placeholder' => give_format_decimal( '1.00' ),
						'value'       => give_format_decimal( $price ),
						'class'       => 'cmb-type-text-small give-money-field',
					),
				),
				//Donation levels: Header
				array(
					'id'   => $prefix . 'levels_header',
					'type' => 'levels_repeater_header',
				),
				//Donation Levels: Repeatable CMB2 Group
				array(
					'id'          => $prefix . 'donation_levels',
					'type'        => 'group',
					'row_classes' => 'give-subfield',
					'options'     => array(
						'add_button'    => esc_html__( 'Add Level', 'give' ),
						'remove_button' => '<span class="dashicons dashicons-no"></span>',
						'sortable'      => true, // beta
					),
					// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
					'fields'      => apply_filters( 'give_donation_levels_table_row', array(
						array(
							'name' => esc_html__( 'ID', 'give' ),
							'id'   => $prefix . 'id',
							'type' => 'levels_id',
						),
						array(
							'name'              => esc_html__( 'Amount', 'give' ),
							'id'                => $prefix . 'amount',
							'type'              => 'text_small',
							'before_field'      => give_get_option( 'currency_position' ) == 'before' ? '<span class="give-money-symbol  give-money-symbol-before">' . give_currency_symbol() . '</span>' : '',
							'after_field'       => give_get_option( 'currency_position' ) == 'after' ? '<span class="give-money-symbol  give-money-symbol-after">' . give_currency_symbol() . '</span>' : '',
							'sanitization_cb'   => 'give_sanitize_price_field_value',
                            'attributes'        => array(
								'placeholder' => give_format_decimal( '1.00' ),
								'class'       => 'cmb-type-text-small give-money-field',
							),
							'before'       => 'give_format_admin_multilevel_amount',
						),
						array(
							'name'       => esc_html__( 'Text', 'give' ),
							'id'         => $prefix . 'text',
							'type'       => 'text',
							'attributes' => array(
								'placeholder' => esc_html__( 'Donation Level', 'give' ),
								'class'       => 'give-multilevel-text-field',
							),
						),
						array(
							'name' => esc_html__( 'Default', 'give' ),
							'id'   => $prefix . 'default',
							'type' => 'give_default_radio_inline'
						),
					) ),
				),
				//Display Style
				array(
					'name'        => esc_html__( 'Display Style', 'give' ),
					'description' => esc_html__( 'Set how the donations levels will display on the form.', 'give' ),
					'id'          => $prefix . 'display_style',
					'type'        => 'radio_inline',
					'default'     => 'buttons',
					'options'     => array(
						'buttons'  => esc_html__( 'Buttons', 'give' ),
						'radios'   => esc_html__( 'Radios', 'give' ),
						'dropdown' => esc_html__( 'Dropdown', 'give' ),
					),
				),
				//Custom Amount
				array(
					'name'        => esc_html__( 'Custom Amount', 'give' ),
					'description' => esc_html__( 'Do you want the user to be able to input their own donation amount?', 'give' ),
					'id'          => $prefix . 'custom_amount',
					'type'        => 'radio_inline',
					'default'     => 'no',
					'options'     => array(
						'yes' => esc_html__( 'Yes', 'give' ),
						'no'  => esc_html__( 'No', 'give' ),
					),
				),
				array(
					'name'              => esc_html__( 'Custom Amount Minimum', 'give' ),
					'description'       => esc_html__( 'If you would like to set a minimum custom donation amount please enter it here.', 'give' ),
					'id'                => $prefix . 'custom_amount_minimum',
					'type'              => 'text_small',
					'row_classes'       => 'give-subfield',
					'render_row_cb'     => 'give_cmb_amount_field_render_row_cb',
					'sanitization_cb'   => 'give_sanitize_price_field_value',
					'attributes'   => array(
						'placeholder' => give_format_decimal('1.00'),
						'value'       => give_format_decimal( $custom_amount_minimum ),
						'class'       => 'cmb-type-text-small give-money-field',
					)
				),
				array(
					'name'        => esc_html__( 'Custom Amount Text', 'give' ),
					'description' => esc_html__( 'This text appears as a label below the custom amount field for set donation forms. For multi-level forms the text will appear as it\'s own level (ie button, radio, or select option).', 'give' ),
					'id'          => $prefix . 'custom_amount_text',
					'type'        => 'text',
					'row_classes' => 'give-subfield',
					'attributes'  => array(
						'rows'        => 3,
						'placeholder' => esc_attr__( 'Give a Custom Amount', 'give' ),
					),
				),
				//Goals
				array(
					'name'        => esc_html__( 'Goal', 'give' ),
					'description' => esc_html__( 'Do you want to set a donation goal for this form?', 'give' ),
					'id'          => $prefix . 'goal_option',
					'type'        => 'radio_inline',
					'default'     => 'no',
					'options'     => array(
						'yes' => esc_html__( 'Yes', 'give' ),
						'no'  => esc_html__( 'No', 'give' ),
					),
				),
				array(
					'name'              => esc_html__( 'Goal Amount', 'give' ),
					'description'       => esc_html__( 'This is the monetary goal amount you want to reach for this donation form.', 'give' ),
					'id'                => $prefix . 'set_goal',
					'type'              => 'text_small',
					'row_classes'       => 'give-subfield',
					'render_row_cb' 	=> 'give_cmb_amount_field_render_row_cb',
					'sanitization_cb'   => 'give_sanitize_price_field_value',
					'attributes'        => array(
						'placeholder' => give_format_decimal( '0.00' ),
						'value'       => give_format_decimal( $goal ),
						'class'       => 'cmb-type-text-small give-money-field',
					),
				),

				array(
					'name'        => esc_html__( 'Goal Format', 'give' ),
					'description' => esc_html__( 'Would you like to display the total amount raised based on your monetary goal or a percentage? For instance, "$500 of $1,000 raised" or "50% funded".', 'give' ),
					'id'          => $prefix . 'goal_format',
					'type'        => 'radio_inline',
					'default'     => 'amount',
					'row_classes' => 'give-subfield',
					'options'     => array(
						'amount'     => esc_html__( 'Amount', 'give' ),
						'percentage' => esc_html__( 'Percentage', 'give' ),
					),
				),
				array(
					'name'        => esc_html__( 'Goal Progress Bar Color', 'give' ),
					'id'          => $prefix . 'goal_color',
					'type'        => 'colorpicker',
					'row_classes' => 'give-subfield',
					'default'     => '#2bc253',
				),

				array(
					'name'        => esc_html__( 'Close Form when Goal Achieved', 'give' ),
					'desc'        => esc_html__( 'Would you like to close the donation forms and stop accepting donations once this goal has been met?', 'give' ),
					'id'          => $prefix . 'close_form_when_goal_achieved',
					'type'        => 'radio_inline',
					'row_classes' => 'give-subfield',
					'options'     => array(
						'yes' => esc_html__( 'Yes', 'give' ),
						'no'  => esc_html__( 'No', 'give' ),
					),
					'default'     => 'no',
				),
				array(
					'name'        => esc_html__( 'Goal Achieved Message', 'give' ),
					'desc'        => esc_html__( 'Would you like to display a custom message when the goal is closed? If none is provided the default message will be displayed', 'give' ),
					'id'          => $prefix . 'form_goal_achieved_message',
					'type'        => 'textarea',
					'row_classes' => 'give-subfield',
					'attributes'  => array(
						'placeholder' => esc_attr__( 'Thank you to all our donors, we have met our fundraising goal.', 'give' ),
					),
				)
			)
		)
	) );


	/**
	 * Content Field
	 */
	$meta_boxes['form_content_options'] = apply_filters( 'give_forms_content_options', array(
		'id'           => 'form_content_options',
		'title'        => esc_html__( 'Form Content', 'give' ),
		'object_types' => array( 'give_forms' ),
		'context'      => 'normal',
		'priority'     => 'high', //Show above Content WYSIWYG
		'fields'       => apply_filters( 'give_forms_content_options_metabox_fields', array(
				//Donation Option
				array(
					'name'        => esc_html__( 'Display Content', 'give' ),
					'description' => esc_html__( 'Do you want to display content? If you choose to display content, a WYSIWYG editor will appear which you will be able to enter content.', 'give' ),
					'id'          => $prefix . 'content_option',
					'type'        => 'select',
					'options'     => apply_filters( 'give_forms_content_options_select', array(
							'none'           => esc_html__( 'No Content', 'give' ),
							'give_pre_form'  => esc_html__( 'Display content ABOVE the fields', 'give' ),
							'give_post_form' => esc_html__( 'Display content BELOW the fields', 'give' ),
						)
					),
					'default'     => 'none',
				),
				array(
					'name'        => esc_html__( 'Content', 'give' ),
					'description' => esc_html__( 'This content will display on the single give form page.', 'give' ),
					'id'          => $prefix . 'form_content',
					'row_classes' => 'give-subfield',
					'type'        => 'wysiwyg'
				),
			)
		)
	) );


	/**
	 * Display Options
	 */
	$meta_boxes['form_display_options'] = apply_filters( 'give_form_display_options', array(
			'id'           => 'form_display_options',
			'title'        => esc_html__( 'Form Display Options', 'give' ),
			'object_types' => array( 'give_forms' ),
			'context'      => 'normal', //  'normal', 'advanced', or 'side'
			'priority'     => 'high', //Show above Content WYSIWYG
			'show_names'   => true, // Show field names on the left
			'fields'       => apply_filters( 'give_forms_display_options_metabox_fields', array(
					array(
						'name'    => esc_html__( 'Payment Fields', 'give' ),
						'desc'    => esc_html__( 'How would you like to display donation information for this form? The "Show on Page" option will display the entire form when the page loads. "Reveal Upon Click" places a button below the donation fields and upon click slides into view the rest of the fields. "Modal Window Upon Click" is a similar option, rather than sliding into view the fields they will open in a shadow box or "modal" window.', 'give' ),
						'id'      => $prefix . 'payment_display',
						'type'    => 'select',
						'options' => array(
							'onpage' => esc_html__( 'Show on Page', 'give' ),
							'reveal' => esc_html__( 'Reveal Upon Click', 'give' ),
							'modal'  => esc_html__( 'Modal Window Upon Click', 'give' ),
						),
						'default' => 'onpage',
					),
					array(
						'id'          => $prefix . 'reveal_label',
						'name'        => esc_html__( 'Reveal / Modal Open Text', 'give' ),
						'desc'        => esc_html__( 'The button label for completing the donation.', 'give' ),
						'type'        => 'text_small',
						'row_classes' => 'give-subfield',
						'attributes'  => array(
							'placeholder' => esc_attr__( 'Donate Now', 'give' ),
						),
					),
					array(
						'id'         => $prefix . 'checkout_label',
						'name'       => esc_html__( 'Complete Donation Text', 'give' ),
						'desc'       => esc_html__( 'The button label for completing the donation.', 'give' ),
						'type'       => 'text_small',
						'attributes' => array(
							'placeholder' => esc_html__( 'Donate Now', 'give' ),
						),
					),
					array(
						'name' => esc_html__( 'Default Gateway', 'give' ),
						'desc' => esc_html__( 'By default, the gateway for this form will inherit the global default gateway (set under Give > Settings > Payment Gateways). This option allows you to customize the default gateway for this form only.', 'give' ),
						'id'   => $prefix . 'default_gateway',
						'type' => 'default_gateway'
					),
					array(
						'name' => esc_html__( 'Disable Guest Donations', 'give' ),
						'desc' => esc_html__( 'Do you want to require users be logged-in to make donations?', 'give' ),
						'id'   => $prefix . 'logged_in_only',
						'type' => 'checkbox'
					),
					array(
						'name'    => esc_html__( 'Register / Login Form', 'give' ),
						'desc'    => esc_html__( 'Display the registration and login forms in the payment section for non-logged-in users.', 'give' ),
						'id'      => $prefix . 'show_register_form',
						'type'    => 'select',
						'options' => array(
							'both'         => esc_html__( 'Registration and Login Forms', 'give' ),
							'registration' => esc_html__( 'Registration Form Only', 'give' ),
							'login'        => esc_html__( 'Login Form Only', 'give' ),
							'none'         => esc_html__( 'None', 'give' ),
						),
						'default' => 'none',
					),
					array(
						'name'    => esc_html__( 'Floating Labels', 'give' ),
						/* translators: %s: forms https://givewp.com/documentation/core/give-forms/creating-give-forms/#floating-labels */
						'desc'    => sprintf( __( 'Select the <a href="%s" target="_blank">floating labels</a> setting for this Give form.<br>Be aware that if you have the "Disable CSS" option enabled, you will need to style the floating labels yourself.', 'give' ), esc_url( 'https://givewp.com/documentation/core/give-forms/creating-give-forms/#floating-labels' ) ),
						'id'      => $prefix . 'form_floating_labels',
						'type'    => 'select',
						'options' => array(
							''         => esc_html__( 'Use the global setting', 'give' ),
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						),
						'default' => 'none',
					)
				)
			)
		)
	);

	/**
	 * Terms & Conditions
	 */
	$meta_boxes['form_terms_options'] = apply_filters( 'give_forms_terms_options', array(
		'id'           => 'form_terms_options',
		'title'        => esc_html__( 'Terms and Conditions', 'give' ),
		'object_types' => array( 'give_forms' ),
		'context'      => 'normal',
		'priority'     => 'high', //Show above Content WYSIWYG
		'fields'       => apply_filters( 'give_forms_terms_options_metabox_fields', array(
				//Donation Option
				array(
					'name'        => esc_html__( 'Terms and Conditions', 'give' ),
					'description' => esc_html__( 'Do you want to require the user to agree to terms and conditions prior to being able to complete their donation?', 'give' ),
					'id'          => $prefix . 'terms_option',
					'type'        => 'select',
					'options'     => apply_filters( 'give_forms_content_options_select', array(
							'none' => esc_html__( 'No', 'give' ),
							'yes'  => esc_html__( 'Yes', 'give' ),
						)
					),
					'default'     => 'none',
				),
				array(
					'id'          => $prefix . 'agree_label',
					'name'        => esc_html__( 'Agree to Terms Label', 'give' ),
					'desc'        => esc_html__( 'The label shown next to the agree to terms check box. Add your own to customize or leave blank to use the default text placeholder.', 'give' ),
					'type'        => 'text',
					'row_classes' => 'give-subfield',
					'size'        => 'regular',
					'attributes'  => array(
						'placeholder' => esc_attr__( 'Agree to Terms?', 'give' ),
					),
				),
				array(
					'id'          => $prefix . 'agree_text',
					'row_classes' => 'give-subfield',
					'name'        => esc_html__( 'Agreement Text', 'give' ),
					'desc'        => esc_html__( 'This is the actual text which the user will have to agree to in order to make a donation.', 'give' ),
					'type'        => 'wysiwyg'
				),
			)
		)
	) );

	foreach ( $meta_boxes as $box ) {
		$cmb = new_cmb2_box( $box );
	}

}

/**
 * Repeatable Levels Custom Field
 */
function give_cmb_render_levels_repeater_header() {
	?>

	<div class="table-container">
		<div class="table-row">
			<div class="table-cell col-amount"><?php esc_html_e( 'Amount', 'give' ); ?></div>
			<div class="table-cell col-text"><?php esc_html_e( 'Text', 'give' ); ?></div>
			<div class="table-cell col-default"><?php esc_html_e( 'Default', 'give' ); ?></div>
			<?php
			/**
			 * Fires in repeatable donation levels table head.
			 *
			 * When selecting the "Multi-level Donation" options, the user see a repeatable fields.
			 * The field is a table with four headers: Amount, Text, Default, Sort.
			 * This action allows you to add more colomn headers to the donation levels table.
			 * New header will be presented before the "Sort" header.
			 *
			 * @since 1.0
			 */
			do_action( 'give_donation_levels_table_head' );
			?>
			<div class="table-cell col-sort"><?php esc_html_e( 'Sort', 'give' ); ?></div>

		</div>
	</div>

	<?php
}

add_action( 'cmb2_render_levels_repeater_header', 'give_cmb_render_levels_repeater_header', 10 );


/**
 *
 * CMB2 Repeatable ID Field
 *
 * Custom CMB2 incremental Levels ID Field
 *
 * @since      1.0
 *
 * @param $field_object
 * @param $escaped_value
 * @param $object_id
 * @param $object_type
 * @param $field_type_object
 */
function give_cmb_render_levels_id( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$escaped_value = ( isset( $escaped_value['level_id'] ) ? $escaped_value['level_id'] : '' );

	$field_options_array = array(
		'class' => 'give-hidden give-level-id-input',
		'name'  => $field_type_object->_name( '[level_id]' ),
		'id'    => $field_type_object->_id( '_level_id' ),
		'value' => $escaped_value,
		'type'  => 'number',
		'desc'  => '',
	);

	echo '<p class="give-level-id">' . $escaped_value . '</p>';
	echo $field_type_object->input( $field_options_array );

}

add_action( 'cmb2_render_levels_id', 'give_cmb_render_levels_id', 10, 5 );


/**
 * Default Radio Inline
 *
 * @param $field_object
 * @param $escaped_value
 * @param $object_id
 * @param $object_type
 * @param $field_type_object
 */
function give_cmb_give_default_radio_inline( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {
	echo '<input type="radio" class="cmb2-option donation-level-radio" name="' . $field_object->args['_name'] . '" id="' . $field_object->args['id'] . '" value="default" ' . checked( 'default', $escaped_value, false ) . '>';
	echo '<label for="' . $field_object->args['id'] . '">' . esc_html__( 'Default', 'give' ) . '</label>';

}

add_action( 'cmb2_render_give_default_radio_inline', 'give_cmb_give_default_radio_inline', 10, 5 );


/**
 * Add Shortcode Copy Field to Publish Metabox
 *
 * @since: 1.0
 */
function give_add_shortcode_to_publish_metabox() {

	if ( 'give_forms' !== get_post_type() ) {
		return false;
	}

	global $post;

	//Only enqueue scripts for CPT on post type screen
	if ( 'give_forms' === $post->post_type ) {
		//Shortcode column with select all input
		$shortcode = htmlentities( '[give_form id="' . $post->ID . '"]' );
		echo '<div class="shortcode-wrap box-sizing"><label for="shortcode-input">' . esc_html__( 'Give Form Shortcode:', 'give' ) . '</label><input onClick="this.setSelectionRange(0, this.value.length)" type="text" name="shortcode-input" id="shortcode-input" class="shortcode-input" readonly value="' . $shortcode . '"></div>';

	}

}

add_action( 'post_submitbox_misc_actions', 'give_add_shortcode_to_publish_metabox' );
