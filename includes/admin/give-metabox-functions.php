<?php
/**
 * Give Meta Box Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Check if field callback exist or not.
 *
 * @since  1.8
 *
 * @param  $field
 *
 * @return bool|string
 */
function give_is_field_callback_exist( $field ) {
	return ( give_get_field_callback( $field ) ? true : false );
}

/**
 * Get field callback.
 *
 * @since  1.8
 *
 * @param  $field
 *
 * @return bool|string
 */
function give_get_field_callback( $field ) {
	$func_name_prefix = 'give';
	$func_name        = '';

	// Set callback function on basis of cmb2 field name.
	switch ( $field['type'] ) {
		case 'radio_inline':
			$func_name = "{$func_name_prefix}_radio";
			break;

		case 'text':
		case 'text-medium':
		case 'text_medium':
		case 'text-small' :
		case 'text_small' :
			$func_name = "{$func_name_prefix}_text_input";
			break;


		case 'textarea' :
			$func_name = "{$func_name_prefix}_textarea_input";
			break;

		case 'colorpicker' :
			$func_name = "{$func_name_prefix}_{$field['type']}";
			break;

		case 'levels_id':
			$func_name = "{$func_name_prefix}_hidden_input";
			break;

		case 'group' :
			$func_name = "_{$func_name_prefix}_metabox_form_data_repeater_fields";
			break;

		case 'give_default_radio_inline':
			$func_name = "{$func_name_prefix}_radio";
			break;

		default:
			$func_name = "{$func_name_prefix}_{$field['type']}";
	}

	$func_name = apply_filters( 'give_setting_callback', $func_name, $field );

	// Check if render callback exist or not.
	if ( ! function_exists( "$func_name" ) || empty( $func_name ) ) {
		return false;
	}

	return apply_filters( 'give_setting_callback', $func_name, $field );
}

/**
 * This function add backward compatibility to render cmb2 type field type.
 *
 * @since  1.8
 *
 * @param  array $field Field argument array.
 *
 * @return bool
 */
function give_render_field( $field ) {
	$func_name = give_get_field_callback( $field );

	// Check if render callback exist or not.
	if ( ! $func_name ) {
		return false;
	}

	// CMB2 compatibility: Push all classes to attributes's class key
	if ( empty( $field['class'] ) ) {
		$field['class'] = '';
	}

	if ( empty( $field['attributes']['class'] ) ) {
		$field['attributes']['class'] = '';
	}

	$field['attributes']['class'] = trim( "give-field {$field['attributes']['class']} give-{$field['type']} {$field['class']}" );
	unset( $field['class'] );


	// CMB2 compatibility: Set wrapper class if any.
	if ( ! empty( $field['row_classes'] ) ) {
		$field['wrapper_class'] = $field['row_classes'];
		unset( $field['row_classes'] );
	}

	// Set field params on basis of cmb2 field name.
	switch ( $field['type'] ) {
		case 'radio_inline':
			if ( empty( $field['wrapper_class'] ) ) {
				$field['wrapper_class'] = '';
			}
			$field['wrapper_class'] .= ' give-inline-radio-fields';

			break;

		case 'text':
		case 'text-medium':
		case 'text_medium':
		case 'text-small' :
		case 'text_small' :
			// CMB2 compatibility: Set field type to text.
			$field['type'] = isset( $field['attributes']['type'] ) ? $field['attributes']['type'] : 'text';

			// CMB2 compatibility: Set data type to price.
			if (
				empty( $field['data_type'] )
				&& ! empty( $field['attributes']['class'] )
				&& (
					false !== strpos( $field['attributes']['class'], 'money' )
					|| false !== strpos( $field['attributes']['class'], 'amount' )
				)
			) {
				$field['data_type'] = 'decimal';
			}
			break;

		case 'levels_id':
			$field['type'] = 'hidden';
			break;

		case 'colorpicker' :
			$field['type']  = 'text';
			$field['class'] = 'give-colorpicker';
			break;

		case 'give_default_radio_inline':
			$field['type']    = 'radio';
			$field['options'] = array(
				'default' => __( 'Default' ),
			);
			break;
	}

	// CMB2 compatibility: Add support to define field description by desc & description param.
	// We encourage you to use description param.
	$field['description'] = ( ! empty( $field['description'] )
		? $field['description']
		: ( ! empty( $field['desc'] ) ? $field['desc'] : '' ) );

	// Call render function.
	$func_name( $field );

	return true;
}

/**
 * Output a text input box.
 *
 * @since  1.8
 * @param  array $field {
 *     Optional. Array of text input field arguments.
 *
 *     @type string             $id              Field ID. Default ''.
 *     @type string             $style           CSS style for input field. Default ''.
 *     @type string             $wrapper_class   CSS class to use for wrapper of input field. Default ''.
 *     @type string             $value           Value of input field. Default ''.
 *     @type string             $name            Name of input field. Default ''.
 *     @type string             $type            Type of input field. Default 'text'.
 *     @type string             $before_field    Text/HTML to add before input field. Default ''.
 *     @type string             $after_field     Text/HTML to add after input field. Default ''.
 *     @type string             $data_type       Define data type for value of input to filter it properly. Default ''.
 *     @type string             $description     Description of input field. Default ''.
 *     @type array              $attributes      List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class' => '****' )
 * }
 * @return void
 */
function give_text_input( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
	$field['before_field']  = '';
	$field['after_field']   = '';
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

	switch ( $data_type ) {
		case 'price' :
			$field['value'] = ( ! empty( $field['value'] ) ? give_format_amount( $field['value'] ) : $field['value'] );

			$field['before_field'] = ! empty( $field['before_field'] ) ? $field['before_field'] : ( give_get_option( 'currency_position' ) == 'before' ? '<span class="give-money-symbol give-money-symbol-before">' . give_currency_symbol() . '</span>' : '' );
			$field['after_field']  = ! empty( $field['after_field'] ) ? $field['after_field'] : ( give_get_option( 'currency_position' ) == 'after' ? '<span class="give-money-symbol give-money-symbol-after">' . give_currency_symbol() . '</span>' : '' );
			break;

		case 'decimal' :
			$field['attributes']['class'] .= ' give_input_decimal';
			$field['value'] = ( ! empty( $field['value'] ) ? give_format_decimal( $field['value'] ) : $field['value'] );
			break;

		default :
			break;
	}

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . give_get_field_name( $field ) . '">' . wp_kses_post( $field['name'] ) . '</label>' . $field['before_field'] . '<input type="' . esc_attr( $field['type'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . give_get_field_name( $field ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />' . $field['after_field'];

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="give-field-description">' . wp_kses_post( $field['description'] ) . '</span>';
	}
	echo '</p>';
}

/**
 * Output a hidden input box.
 *
 * @since  1.8
 * @param  array $field {
 *     Optional. Array of hidden text input field arguments.
 *
 *     @type string             $id              Field ID. Default ''.
 *     @type string             $value           Value of input field. Default ''.
 *     @type string             $name            Name of input field. Default ''.
 *     @type string             $type            Type of input field. Default 'text'.
 *     @type array              $attributes      List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class' => '****' )
 * }
 * @return void
 */
function give_hidden_input( $field ) {
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<input type="hidden" name="' . give_get_field_name( $field ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" ' . implode( ' ', $custom_attributes ) . '/> ';
}

/**
 * Output a textarea input box.
 *
 * @since  1.8
 * @since  1.8
 * @param  array $field {
 *     Optional. Array of textarea input field arguments.
 *
 *     @type string             $id              Field ID. Default ''.
 *     @type string             $style           CSS style for input field. Default ''.
 *     @type string             $wrapper_class   CSS class to use for wrapper of input field. Default ''.
 *     @type string             $value           Value of input field. Default ''.
 *     @type string             $name            Name of input field. Default ''.
 *     @type string             $description     Description of input field. Default ''.
 *     @type array              $attributes      List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class' => '****' )
 * }
 * @return void
 */
function give_textarea_input( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . give_get_field_name( $field ) . '">' . wp_kses_post( $field['name'] ) . '</label><textarea style="' . esc_attr( $field['style'] ) . '"  name="' . give_get_field_name( $field ) . '" id="' . esc_attr( $field['id'] ) . '" rows="10" cols="20" ' . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $field['value'] ) . '</textarea> ';

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="give-field-description">' . wp_kses_post( $field['description'] ) . '</span>';
	}
	echo '</p>';
}

/**
 * Output a wysiwyg.
 *
 * @since  1.8
 * @param  array $field {
 *     Optional. Array of WordPress editor field arguments.
 *
 *     @type string             $id              Field ID. Default ''.
 *     @type string             $style           CSS style for input field. Default ''.
 *     @type string             $wrapper_class   CSS class to use for wrapper of input field. Default ''.
 *     @type string             $value           Value of input field. Default ''.
 *     @type string             $name            Name of input field. Default ''.
 *     @type string             $description     Description of input field. Default ''.
 *     @type array              $attributes      List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class' => '****' )
 * }
 * @return void
 */
function give_wysiwyg( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	// Add backward compatibility to cmb2 attributes.
	$custom_attributes = array_merge(
		array(
			'textarea_name' => esc_attr( $field['id'] ),
			'textarea_rows' => '10',
			'editor_css'    => esc_attr( $field['style'] ),
		),
		$custom_attributes
	);

	echo '<div class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . give_get_field_name( $field ) . '">' . wp_kses_post( $field['name'] ) . '</label>';

	wp_editor(
		$field['value'],
		give_get_field_name( $field ),
		$custom_attributes
	);

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="give-field-description">' . wp_kses_post( $field['description'] ) . '</span>';
	}
	echo '</div>';
}

/**
 * Output a checkbox input box.
 *
 * @since  1.8
 * @param  array $field {
 *     Optional. Array of checkbox field arguments.
 *
 *     @type string             $id              Field ID. Default ''.
 *     @type string             $style           CSS style for input field. Default ''.
 *     @type string             $wrapper_class   CSS class to use for wrapper of input field. Default ''.
 *     @type string             $value           Value of input field. Default ''.
 *     @type string             $cbvalue         Checkbox value. Default 'on'.
 *     @type string             $name            Name of input field. Default ''.
 *     @type string             $description     Description of input field. Default ''.
 *     @type array              $attributes      List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class' => '****' )
 * }
 * @return void
 */
function give_checkbox( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'on';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	// Custom attribute handling.
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . give_get_field_name( $field ) . '">' . wp_kses_post( $field['name'] ) . '</label><input type="checkbox" style="' . esc_attr( $field['style'] ) . '" name="' . give_get_field_name( $field ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . '  ' . implode( ' ', $custom_attributes ) . '/> ';

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="give-field-description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</p>';
}

/**
 * Output a select input box.
 *
 * @since  1.8
 * @param  array $field {
 *     Optional. Array of select field arguments.
 *
 *     @type string             $id              Field ID. Default ''.
 *     @type string             $style           CSS style for input field. Default ''.
 *     @type string             $wrapper_class   CSS class to use for wrapper of input field. Default ''.
 *     @type string             $value           Value of input field. Default ''.
 *     @type string             $name            Name of input field. Default ''.
 *     @type string             $description     Description of input field. Default ''.
 *     @type array              $attributes      List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class' => '****' )
 *     @type array              $options         List of options. Default array().
 *                                               for example: 'options' => array( '' => 'None', 'yes' => 'Yes' )
 * }
 * @return void
 */
function give_select( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . give_get_field_name( $field ) . '">' . wp_kses_post( $field['name'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . give_get_field_name( $field ) . '" style="' . esc_attr( $field['style'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';

	foreach ( $field['options'] as $key => $value ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
	}

	echo '</select> ';

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="give-field-description">' . wp_kses_post( $field['description'] ) . '</span>';
	}
	echo '</p>';
}

/**
 * Output a radio input box.
 *
 * @since  1.8
 * @param  array $field {
 *     Optional. Array of radio field arguments.
 *
 *     @type string             $id              Field ID. Default ''.
 *     @type string             $style           CSS style for input field. Default ''.
 *     @type string             $wrapper_class   CSS class to use for wrapper of input field. Default ''.
 *     @type string             $value           Value of input field. Default ''.
 *     @type string             $name            Name of input field. Default ''.
 *     @type string             $description     Description of input field. Default ''.
 *     @type array              $attributes      List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class' => '****' )
 *     @type array              $options         List of options. Default array().
 *                                               for example: 'options' => array( 'enable' => 'Enable', 'disable' => 'Disable' )
 * }
 * @return void
 */
function give_radio( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<fieldset class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><legend>' . wp_kses_post( $field['name'] ) . '</legend><ul class="give-radios">';

	foreach ( $field['options'] as $key => $value ) {

		echo '<li><label><input
				name="' . give_get_field_name( $field ) . '"
				value="' . esc_attr( $key ) . '"
				type="radio"
				style="' . esc_attr( $field['style'] ) . '"
				' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . ' '
				. implode( ' ', $custom_attributes ) . '
				/> ' . esc_html( $value ) . '</label>
		</li>';
	}
	echo '</ul>';

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="give-field-description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</fieldset>';
}

/**
 * Output a colorpicker.
 *
 * @since  1.8
 * @param  array $field {
 *     Optional. Array of colorpicker field arguments.
 *
 *     @type string             $id              Field ID. Default ''.
 *     @type string             $style           CSS style for input field. Default ''.
 *     @type string             $wrapper_class   CSS class to use for wrapper of input field. Default ''.
 *     @type string             $value           Value of input field. Default ''.
 *     @type string             $name            Name of input field. Default ''.
 *     @type string             $description     Description of input field. Default ''.
 *     @type array              $attributes      List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class' => '****' )
 * }
 * @return void
 */
function give_colorpicker( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['type']          = 'text';

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . give_get_field_name( $field ) . '">' . wp_kses_post( $field['name'] ) . '</label><input type="' . esc_attr( $field['type'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . give_get_field_name( $field ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="give-field-description">' . wp_kses_post( $field['description'] ) . '</span>';
	}
	echo '</p>';
}

/**
 * Output a select field with payment options list.
 *
 * @since  1.8
 *
 * @param  array $field
 *
 * @return void
 */
function give_default_gateway( $field ) {
	global $thepostid, $post;

	// get all active payment gateways.
	$gateways = give_get_enabled_payment_gateways( $thepostid );

	// Set field option value.
	foreach ( $gateways as $key => $option ) {
		$field['options'][ $key ] = $option['admin_label'];
	}

	//Add a field to the Give Form admin single post view of this field
	if ( is_object( $post ) && 'give_forms' === $post->post_type ) {
		$field['options'] = array_merge( array( 'global' => esc_html__( 'Global Default', 'give' ) ), $field['options'] );
	}

	// Render select field.
	give_select( $field );
}


/**
 * Get setting field value.
 *
 * Note: Use only for single post, page or custom post type.
 *
 * @since  1.8
 *
 * @param  array $field
 * @param  int   $postid
 *
 * @return mixed
 */
function give_get_field_value( $field, $postid ) {
	if ( isset( $field['attributes']['value'] ) ) {
		return $field['attributes']['value'];
	}

	// Get value from db.
	$field_value = get_post_meta( $postid, $field['id'], true );

	/**
	 * Filter the field value before apply default value.
	 *
	 * @since 1.8
	 *
	 * @param mixed $field_value Field value.
	 */
	$field_value = apply_filters( "{$field['id']}_field_value", $field_value, $field, $postid );


	// Set default value if no any data saved to db.
	if ( ! $field_value && isset( $field['default'] ) ) {
		$field_value = $field['default'];
	}

	return $field_value;
}

/**
 * Get repeater field value.
 *
 * Note: Use only for single post, page or custom post type.
 *
 * @since  1.8
 *
 * @param array $field
 * @param array $field_group
 * @param array $fields
 *
 * @return string
 */
function give_get_repeater_field_value( $field, $field_group, $fields ) {
	$field_value = ( isset( $field_group[ $field['id'] ] ) ? $field_group[ $field['id'] ] : '' );

	/**
	 * Filter the specific repeater field value
	 *
	 * @since 1.8
	 *
	 * @param string $field_id
	 */
	$field_value = apply_filters( "give_get_repeater_field_{$field['id']}_value", $field_value, $field, $field_group, $fields );

	/**
	 * Filter the repeater field value
	 *
	 * @since 1.8
	 *
	 * @param string $field_id
	 */
	$field_value = apply_filters( 'give_get_repeater_field_value', $field_value, $field, $field_group, $fields );

	return $field_value;
}

/**
 * Get repeater field id.
 *
 * Note: Use only for single post, page or custom post type.
 *
 * @since  1.8
 *
 * @param array    $field
 * @param array    $fields
 * @param int|bool $default
 *
 * @return string
 */
function give_get_repeater_field_id( $field, $fields, $default = false ) {
	$row_placeholder = false !== $default ? $default : '{{row-count-placeholder}}';

	// Get field id.
	$field_id = "{$fields['id']}[{$row_placeholder}][{$field['id']}]";

	/**
	 * Filter the specific repeater field id
	 *
	 * @since 1.8
	 *
	 * @param string $field_id
	 */
	$field_id = apply_filters( "give_get_repeater_field_{$field['id']}_id", $field_id, $field, $fields, $default );


	/**
	 * Filter the repeater field id
	 *
	 * @since 1.8
	 *
	 * @param string $field_id
	 */
	$field_id = apply_filters( 'give_get_repeater_field_id', $field_id, $field, $fields, $default );

	return $field_id;
}


/**
 * Get field name.
 *
 * @since  1.8
 *
 * @param  array $field
 *
 * @return string
 */
function give_get_field_name( $field ) {
	return esc_attr( empty( $field['repeat'] ) ? $field['id'] : $field['repeatable_field_id'] );
}

/**
 * Output repeater field or multi donation type form on donation from edit screen.
 * Note: internal use only.
 * @TODO   : Add support for wysiwyg tytpe field.
 *
 * @since  1.8
 *
 * @param  array $fields
 *
 * @return void
 */
function _give_metabox_form_data_repeater_fields( $fields ) {
	global $thepostid, $post;

	// Bailout.
	if ( ! isset( $fields['fields'] ) || empty( $fields['fields'] ) ) {
		return;
	}

	$group_numbering = isset( $fields['options']['group_numbering'] ) ? (int) $fields['options']['group_numbering'] : 0;
	?>
	<div class="give-repeatable-field-section" id="<?php echo "{$fields['id']}_field"; ?>" data-group-numbering="<?php echo $group_numbering; ?>">
		<?php if ( ! empty( $fields['name'] ) ) : ?>
			<p class="give-repeater-field-name"><?php echo $fields['name']; ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $fields['description'] ) ) : ?>
			<p class="give-repeater-field-description"><?php echo $fields['description']; ?></p>
		<?php endif; ?>

		<table class="give-repeatable-fields-section-wrapper" cellspacing="0">
			<?php
			$repeater_field_values = get_post_meta( $thepostid, $fields['id'], true );
			$header_title          = isset( $fields['options']['header_title'] )
				? $fields['options']['header_title']
				: esc_attr__( 'Group', 'give' );

			$add_default_donation_field = false;

			// Check if level is not created or we have to add default level.
			if ( is_array( $repeater_field_values ) && ( $fields_count = count( $repeater_field_values ) ) ) {
				$repeater_field_values = array_values( $repeater_field_values );
			} else {
				$fields_count               = 1;
				$add_default_donation_field = true;
			}
			?>
			<tbody class="container"<?php echo " data-rf-row-count=\"{$fields_count}\""; ?>>
				<!--Repeater field group template-->
				<tr class="give-template give-row">
					<td class="give-repeater-field-wrap give-column" colspan="2">
						<div class="give-row-head give-move">
							<button type="button" class="handlediv button-link"><span class="toggle-indicator"></span>
							</button>
							<sapn class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-</sapn>
							<h2>
								<span data-header-title="<?php echo $header_title; ?>"><?php echo $header_title; ?></span>
							</h2>
						</div>
						<div class="give-row-body">
							<?php foreach ( $fields['fields'] as $field ) : ?>
								<?php if ( ! give_is_field_callback_exist( $field ) ) {
									continue;
								} ?>
								<?php
								$field['repeat']              = true;
								$field['repeatable_field_id'] = give_get_repeater_field_id( $field, $fields );
								$field['id']                  = str_replace( array( '[', ']' ), array(
									'_',
									'',
								), $field['repeatable_field_id'] );
								?>
								<?php give_render_field( $field ); ?>
							<?php endforeach; ?>
						</div>
					</td>
				</tr>

				<?php if ( ! empty( $repeater_field_values ) ) : ?>
					<!--Stored repeater field group-->
					<?php foreach ( $repeater_field_values as $index => $field_group ) : ?>
						<tr class="give-row">
							<td class="give-repeater-field-wrap give-column" colspan="2">
								<div class="give-row-head give-move">
									<button type="button" class="handlediv button-link">
										<span class="toggle-indicator"></span></button>
									<sapn class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-
									</sapn>
									<h2>
										<span data-header-title="<?php echo $header_title; ?>"><?php echo $header_title; ?></span>
									</h2>
								</div>
								<div class="give-row-body">
									<?php foreach ( $fields['fields'] as $field ) : ?>
										<?php if ( ! give_is_field_callback_exist( $field ) ) {
											continue;
										} ?>
										<?php
										$field['repeat']              = true;
										$field['repeatable_field_id'] = give_get_repeater_field_id( $field, $fields, $index );
										$field['attributes']['value'] = give_get_repeater_field_value( $field, $field_group, $fields );
										$field['id']                  = str_replace( array( '[', ']' ), array(
											'_',
											'',
										), $field['repeatable_field_id'] );
										?>
										<?php give_render_field( $field ); ?>
									<?php endforeach; ?>
								</div>
							</td>
						</tr>
					<?php endforeach;; ?>

				<?php elseif ( $add_default_donation_field ) : ?>
					<!--Default repeater field group-->
					<tr class="give-row">
						<td class="give-repeater-field-wrap give-column" colspan="2">
							<div class="give-row-head give-move">
								<button type="button" class="handlediv button-link">
									<span class="toggle-indicator"></span></button>
								<sapn class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-
								</sapn>
								<h2>
									<span data-header-title="<?php echo $header_title; ?>"><?php echo $header_title; ?></span>
								</h2>
							</div>
							<div class="give-row-body">
								<?php
								foreach ( $fields['fields'] as $field ) :
									if ( ! give_is_field_callback_exist( $field ) ) {
										continue;
									}

									$field['repeat']              = true;
									$field['repeatable_field_id'] = give_get_repeater_field_id( $field, $fields, true );
									$field['attributes']['value'] = apply_filters( "give_default_field_group_field_{$field['id']}_value", ( ! empty( $field['default'] ) ? $field['default'] : '' ), $field );
									$field['id']                  = str_replace( array( '[', ']' ), array(
										'_',
										'',
									), $field['repeatable_field_id'] );
									give_render_field( $field );
								endforeach;
								?>
							</div>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<tr>
					<?php
					$add_row_btn_title = isset( $fields['options']['add_button'] )
						? $add_row_btn_title = $fields['options']['add_button']
						: esc_html__( 'Add Row', 'give' );
					?>
					<td colspan="2" class="give-add-repeater-field-section-row-wrap">
						<span class="button button-primary give-add-repeater-field-section-row"><?php echo $add_row_btn_title; ?></span>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	<?php
}


/**
 * Get current setting tab.
 *
 * @since  1.8
 * @return string
 */
function give_get_current_setting_tab() {
	// Get current setting page.
	$current_setting_page = give_get_current_setting_page();

	/**
	 * Filter the default tab for current setting page.
	 *
	 * @since 1.8
	 *
	 * @param string
	 */
	$default_current_tab = apply_filters( "give_default_setting_tab_{$current_setting_page}", 'general' );

	// Get current tab.
	$current_tab = empty( $_GET['tab'] ) ? $default_current_tab : urldecode( $_GET['tab'] );

	// Output.
	return $current_tab;
}


/**
 * Get current setting section.
 *
 * @since  1.8
 * @return string
 */
function give_get_current_setting_section() {
	// Get current tab.
	$current_tab = give_get_current_setting_tab();

	/**
	 * Filter the default section for current setting page tab.
	 *
	 * @since 1.8
	 *
	 * @param string
	 */
	$default_current_section = apply_filters( "give_default_setting_tab_section_{$current_tab}", '' );

	// Get current section.
	$current_section = empty( $_REQUEST['section'] ) ? $default_current_section : urldecode( $_REQUEST['section'] );

	//Output.
	return $current_section;
}

/**
 * Get current setting page.
 *
 * @since  1.8
 * @return string
 */
function give_get_current_setting_page() {
	// Get current page.
	$setting_page = ! empty( $_GET['page'] ) ? urldecode( $_GET['page'] ) : '';

	//Output.
	return $setting_page;
}

/**
 * Set value for Form content --> Display content field setting.
 *
 * Backward compatibility:  set value by _give_content_option form meta field value if _give_display_content is not set yet.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_display_content_field_value( $field_value, $field, $postid ) {
	$show_content = get_post_meta( $postid, '_give_content_option', true );

	if (
		! get_post_meta( $postid, '_give_display_content', true )
		&& $show_content
		&& ( 'none' !== $show_content )
	) {
		$field_value = 'enabled';
	}

	return $field_value;
}

add_filter( '_give_display_content_field_value', '_give_display_content_field_value', 10, 3 );


/**
 * Set value for Form content --> Content placement field setting.
 *
 * Backward compatibility:  set value by _give_content_option form meta field value if _give_content_placement is not set yet.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_content_placement_field_value( $field_value, $field, $postid ) {
	$show_content = get_post_meta( $postid, '_give_content_option', true );

	if (
		! get_post_meta( $postid, '_give_content_placement', true )
		&& ( 'none' !== $show_content )
	) {
		$field_value = $show_content;
	}

	return $field_value;
}

add_filter( '_give_content_placement_field_value', '_give_content_placement_field_value', 10, 3 );


/**
 * Set value for Terms and Conditions --> Terms and Conditions field setting.
 *
 * Backward compatibility:  set value by _give_terms_option form meta field value if it's value is none.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_terms_option_field_value( $field_value, $field, $postid ) {
	$term_option = get_post_meta( $postid, '_give_terms_option', true );

	if ( in_array( $term_option, array( 'none', 'yes' ) ) ) {
		$field_value = ( 'yes' === $term_option ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_terms_option_field_value', '_give_terms_option_field_value', 10, 3 );


/**
 * Set value for Form Display --> Offline Donation --> Billing Fields.
 *
 * Backward compatibility:  set value by _give_offline_donation_enable_billing_fields_single form meta field value if it's value is on.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_offline_donation_enable_billing_fields_single_field_value( $field_value, $field, $postid ) {
	$offline_donation = get_post_meta( $postid, '_give_offline_donation_enable_billing_fields_single', true );

	if ( 'on' === $offline_donation ) {
		$field_value = 'enabled';
	}

	return $field_value;
}

add_filter( '_give_offline_donation_enable_billing_fields_single_field_value', '_give_offline_donation_enable_billing_fields_single_field_value', 10, 3 );


/**
 * Set value for Donation Options --> Custom Amount.
 *
 * Backward compatibility:  set value by _give_custom_amount form meta field value if it's value is yes or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_custom_amount_field_value( $field_value, $field, $postid ) {
	$custom_amount = get_post_meta( $postid, '_give_custom_amount', true );

	if ( in_array( $custom_amount, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $custom_amount ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_custom_amount_field_value', '_give_custom_amount_field_value', 10, 3 );


/**
 * Set value for Donation Goal --> Donation Goal.
 *
 * Backward compatibility:  set value by _give_goal_option form meta field value if it's value is yes or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_goal_option_field_value( $field_value, $field, $postid ) {
	$goal_option = get_post_meta( $postid, '_give_goal_option', true );

	if ( in_array( $goal_option, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $goal_option ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_goal_option_field_value', '_give_goal_option_field_value', 10, 3 );

/**
 * Set value for Donation Goal --> close Form.
 *
 * Backward compatibility:  set value by _give_close_form_when_goal_achieved form meta field value if it's value is yes or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_close_form_when_goal_achieved_value( $field_value, $field, $postid ) {
	$close_form = get_post_meta( $postid, '_give_close_form_when_goal_achieved', true );

	if ( in_array( $close_form, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $close_form ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_close_form_when_goal_achieved_field_value', '_give_close_form_when_goal_achieved_value', 10, 3 );


/**
 * Set value for Form display --> Guest Donation.
 *
 * Backward compatibility:  set value by _give_logged_in_only form meta field value if it's value is yes or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_logged_in_only_value( $field_value, $field, $postid ) {
	$guest_donation = get_post_meta( $postid, '_give_logged_in_only', true );

	if ( in_array( $guest_donation, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $guest_donation ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_logged_in_only_field_value', '_give_logged_in_only_value', 10, 3 );

/**
 * Set value for Offline Donations --> Offline Donations.
 *
 * Backward compatibility:  set value by _give_customize_offline_donations form meta field value if it's value is yes or no.
 *
 * @since  1.8
 *
 * @param  mixed $field_value Field Value.
 * @param  array $field       Field args.
 * @param  int   $postid      Form/Post ID.
 *
 * @return string
 */
function _give_customize_offline_donations_value( $field_value, $field, $postid ) {
	$customize_offline_text = get_post_meta( $postid, '_give_customize_offline_donations', true );

	if ( in_array( $customize_offline_text, array( 'yes', 'no' ) ) ) {
		$field_value = ( 'yes' === $customize_offline_text ? 'enabled' : 'disabled' );
	}

	return $field_value;
}

add_filter( '_give_customize_offline_donations_field_value', '_give_customize_offline_donations_value', 10, 3 );


/**
 * Set repeater field id for multi donation form.
 *
 * @since 1.8
 *
 * @param int   $field_id
 * @param array $field
 * @param array $fields
 * @param bool  $default
 *
 * @return mixed
 */
function _give_set_multi_level_repeater_field_id( $field_id, $field, $fields, $default ) {
	if ( ! $default && '_give_id' === $field['id'] && '_give_donation_levels' === $fields['id'] ) {
		$field_id = "{$fields['id']}[{{row-count-placeholder}}][{$field['id']}][level_id]";
	}

	return $field_id;
}

add_filter( 'give_get_repeater_field_id', '_give_set_multi_level_repeater_field_id', 10, 4 );

/**
 * Set repeater field value for multi donation form.
 *
 * @since 1.8
 *
 * @param string $field_value
 * @param array  $field
 * @param array  $field_group
 * @param array  $fields
 *
 * @return mixed
 */
function _give_set_multi_level_repeater_field_value( $field_value, $field, $field_group, $fields ) {
	if ( isset( $field['id'] ) && '_give_id' === $field['id'] && '_give_donation_levels' === $fields['id'] ) {
		$field_value = $field_group[ $field['id'] ]['level_id'];
	}

	return $field_value;
}

add_filter( 'give_get_repeater_field_value', '_give_set_multi_level_repeater_field_value', 10, 4 );

/**
 * Set default value for _give_id field.
 *
 * @since 1.8
 *
 * @param $field
 *
 * @return string
 */
function _give_set_field_give_id_default_value( $field ) {
	return 0;
}

add_filter( 'give_default_field_group_field__give_id_value', '_give_set_field_give_id_default_value' );

/**
 * Set default value for _give_default field.
 *
 * @since 1.8
 *
 * @param $field
 *
 * @return string
 */
function _give_set_field_give_default_default_value( $field ) {
	return 'default';
}

add_filter( 'give_default_field_group_field__give_default_value', '_give_set_field_give_default_default_value' );