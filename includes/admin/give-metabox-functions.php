<?php
/**
 * Give Meta Box Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, GiveWP
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
		case 'text-small':
		case 'text_small':
		case 'number':
		case 'email':
			$func_name = "{$func_name_prefix}_text_input";
			break;

		case 'textarea':
			$func_name = "{$func_name_prefix}_textarea_input";
			break;

		case 'colorpicker':
			$func_name = "{$func_name_prefix}_{$field['type']}";
			break;

		case 'hidden':
		case 'levels_id':
			$func_name = "{$func_name_prefix}_hidden_input";
			break;

		case 'group':
			$func_name = "_{$func_name_prefix}_metabox_form_data_repeater_fields";
			break;

		case 'give_default_radio_inline':
			$func_name = "{$func_name_prefix}_radio";
			break;

		case 'donation_limit':
			$func_name = "{$func_name_prefix}_donation_limit";
			break;

		case 'chosen':
			$func_name = "{$func_name_prefix}_chosen_input";
			break;

		case 'label':
			$func_name = "{$func_name_prefix}_label_field";
			break;

		default:
			if (
				array_key_exists( 'callback', $field )
				&& ! empty( $field['callback'] )
			) {
				$func_name = $field['callback'];
			} else {
				$func_name = "{$func_name_prefix}_{$field['type']}";
			}
	}

	/**
	 * Filter the metabox setting render function
	 *
	 * @since 1.8
	 */
	$func_name = apply_filters( 'give_get_field_callback', $func_name, $field );

	// Exit if not any function exist.
	// Check if render callback exist or not.
	if ( empty( $func_name ) ) {
		return false;
	} elseif ( is_string( $func_name ) && ! function_exists( "$func_name" ) ) {
		return false;
	} elseif ( is_array( $func_name ) && ! method_exists( $func_name[0], "$func_name[1]" ) ) {
		return false;
	}

	return $func_name;
}

/**
 * This function adds backward compatibility to render cmb2 type field type.
 *
 * @since  1.8
 *
 * @param  array $field Field argument array.
 *
 * @return bool
 */
function give_render_field( $field ) {

	// Check if render callback exist or not.
	if ( ! ( $func_name = give_get_field_callback( $field ) ) ) {
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
		case 'text-small':
		case 'text_small':
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

		case 'colorpicker':
			$field['type']  = 'text';
			$field['class'] = 'give-colorpicker';
			break;

		case 'give_default_radio_inline':
			$field['type']    = 'radio';
			$field['options'] = [
				'default' => __( 'Default', 'give' ),
			];
			break;

		case 'donation_limit':
			$field['type'] = 'donation_limit';
			break;
	} // End switch().

	// CMB2 compatibility: Add support to define field description by desc & description param.
	// We encourage you to use description param.
	$field['description'] = ( ! empty( $field['description'] )
		? $field['description']
		: ( ! empty( $field['desc'] ) ? $field['desc'] : '' ) );

	// Call render function.
	if ( is_array( $func_name ) ) {
		$func_name[0]->{$func_name[1]}( $field );
	} else {
		$func_name( $field );
	}

	return true;
}

/**
 * Output a text input box.
 *
 * @since  1.8
 *
 * @param  array $field         {
 *                              Optional. Array of text input field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $type          Type of input field. Default 'text'.
 * @type string  $before_field  Text/HTML to add before input field. Default ''.
 * @type string  $after_field   Text/HTML to add after input field. Default ''.
 * @type string  $data_type     Define data type for value of input to filter it properly. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
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
		case 'price':
			$field['value'] = ( ! empty( $field['value'] ) ? give_format_decimal( give_maybe_sanitize_amount( $field['value'] ), false, false ) : $field['value'] );

			$field['before_field'] = ! empty( $field['before_field'] ) ? $field['before_field'] : ( give_get_option( 'currency_position', 'before' ) == 'before' ? '<span class="give-money-symbol give-money-symbol-before">' . give_currency_symbol() . '</span>' : '' );
			$field['after_field']  = ! empty( $field['after_field'] ) ? $field['after_field'] : ( give_get_option( 'currency_position', 'before' ) == 'after' ? '<span class="give-money-symbol give-money-symbol-after">' . give_currency_symbol() . '</span>' : '' );
			break;

		case 'decimal':
			$field['attributes']['class'] .= ' give_input_decimal';
			$field['value']                = ( ! empty( $field['value'] ) ? give_format_decimal( give_maybe_sanitize_amount( $field['value'] ), false, false ) : $field['value'] );
			break;

		default:
			break;
	}

	?>
	<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
	<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
	<?php echo $field['before_field']; ?>
	<input
			type="<?php echo esc_attr( $field['type'] ); ?>"
			style="<?php echo esc_attr( $field['style'] ); ?>"
			name="<?php echo give_get_field_name( $field ); ?>"
			id="<?php echo esc_attr( $field['id'] ); ?>"
			value="<?php echo esc_attr( $field['value'] ); ?>"
		<?php echo give_get_attribute_str( $field ); ?>
	/>
	<?php echo $field['after_field']; ?>
	<?php
	echo give_get_field_description( $field );
	echo '</p>';
}

/**
 * Output a chosen input box.
 * Note: only for internal use.
 *
 * @param array $field         {
 *                              Optional. Array of text input field arguments.
 *
 * @type string $id            Field ID. Default ''.
 * @type string $style         CSS style for input field. Default ''.
 * @type string $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string $value         Value of input field. Default ''.
 * @type string $name          Name of input field. Default ''.
 * @type string $type          Type of input field. Default 'text'.
 * @type string $before_field  Text/HTML to add before input field. Default ''.
 * @type string $after_field   Text/HTML to add after input field. Default ''.
 * @type string $data_type     Define data type for value of input to filter it properly. Default ''.
 * @type string $description   Description of input field. Default ''.
 * @type array  $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * }
 *
 * @since 2.1
 *
 * @return void
 */
function give_chosen_input( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['before_field']  = '';
	$field['after_field']   = '';
	$placeholder            = isset( $field['placeholder'] ) ? 'data-placeholder="' . $field['placeholder'] . '"' : '';
	$data_type              = ! empty( $field['data_type'] ) ? $field['data_type'] : '';
	$type                   = '';
	$field['value']         = array_filter( (array) give_get_field_value( $field, $thepostid ) );
	$choices                = $field['options'];
	$fieldName              = esc_attr( give_get_field_name( $field ) );
	$allow_new_values       = ! empty( $field['allow-custom-values'] ) && (bool) $field['allow-custom-values'] ? 'data-allows-new-values="true"' : '';

	// Set attributes based on multiselect datatype.
	if ( 'multiselect' === $data_type ) {
		$type           = 'multiple';
		$field['value'] = empty( $field['value'] ) ? [] : $field['value'];
		$fieldName     .= '[]';
	}

	if ( $allow_new_values && $field['value'] ) {
		$choices = array_merge( array_combine( $field['value'], $field['value'] ), $choices );
	}
	?>
	<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<label for="<?php echo esc_attr( give_get_field_name( $field ) ); ?>">
			<?php echo wp_kses_post( $field['name'] ); ?>
		</label>
		<?php echo esc_attr( $field['before_field'] ); ?>
		<select
				class="give-select-chosen give-chosen-settings"
				style="<?php echo esc_attr( $field['style'] ); ?>"
				name="<?php echo $fieldName; ?>"
				id="<?php echo esc_attr( $field['id'] ); ?>"
			<?php echo "{$type} {$allow_new_values} {$placeholder}"; ?>
		>
			<?php
			foreach ( $choices as $key => $name ) {
				echo sprintf(
					'<option %1$s value="%2$s">%3$s</option>',
					in_array( $key, $field['value'] ) ? 'selected="selected"' : '',
					esc_attr( $key ),
					$name
				);
			}
			?>
		</select>
		<?php echo esc_attr( $field['after_field'] ); ?>
		<?php echo give_get_field_description( $field ); ?>
	</p>
	<?php
}

/**
 * Output a label field
 * Note: only for internal use.
 *
 * @param array $field         {
 *                              Optional. Array of text input field arguments.
 *
 * @type string $id            Field ID. Default ''.
 * @type string $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string $value         Value of input field. Default ''.
 * @type string $title         Text to display. HTML supported Default ''.
 * @type string $before_field  Text/HTML to add before input field. Default ''.
 * @type string $after_field   Text/HTML to add after input field. Default ''.
 * }
 *
 * @since 2.7.0
 *
 * @return void
 */
function give_label_field( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['before_field']  = '';
	$field['after_field']   = '';
	?>
	<p class=" <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<?php echo esc_attr( $field['before_field'] ); ?>
		<?php echo $field['title']; ?>
		<?php echo esc_attr( $field['after_field'] ); ?>
		<?php echo give_get_field_description( $field ); ?>
	</p>
	<?php
}

/**
 * Give range slider field.
 * Note: only for internal logic
 *
 * @since 2.1
 *
 * @param  array $field         {
 *                              Optional. Array of text input field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $type          Type of input field. Default 'text'.
 * @type string  $before_field  Text/HTML to add before input field. Default ''.
 * @type string  $after_field   Text/HTML to add after input field. Default ''.
 * @type string  $data_type     Define data type for value of input to filter it properly. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * }
 *
 * @return void
 */
function give_donation_limit( $field ) {
	global $thepostid, $post;

	// Get Give donation form ID.
	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

	// Default arguments.
	$default_options = [
		'style'         => '',
		'wrapper_class' => '',
		'value'         => give_get_field_value( $field, $thepostid ),
		'data_type'     => 'decimal',
		'before_field'  => '',
		'after_field'   => '',
	];

	// Field options.
	$field['options'] = ! empty( $field['options'] ) ? $field['options'] : [];

	// Default field option arguments.
	$field['options'] = wp_parse_args(
		$field['options'],
		[
			'display_label' => '',
			'minimum'       => give_format_decimal( '1.00', false, false ),
			'maximum'       => give_format_decimal( '999999.99', false, false ),
		]
	);

	// Set default field options.
	$field_options = wp_parse_args( $field, $default_options );

	// Get default minimum value, if empty.
	$field_options['value']['minimum'] = ! empty( $field_options['value']['minimum'] )
		? $field_options['value']['minimum']
		: $field_options['options']['minimum'];

	// Get default maximum value, if empty.
	$field_options['value']['maximum'] = ! empty( $field_options['value']['maximum'] )
		? $field_options['value']['maximum']
		: $field_options['options']['maximum'];
	?>
	<p class="give-field-wrap <?php echo esc_attr( $field_options['id'] ); ?>_field <?php echo esc_attr( $field_options['wrapper_class'] ); ?>">
	<label for="<?php echo give_get_field_name( $field_options ); ?>"><?php echo wp_kses_post( $field_options['name'] ); ?></label>
	<span class="give_donation_limit_display">
		<?php
		foreach ( $field_options['value'] as $amount_range => $amount_value ) {

			switch ( $field_options['data_type'] ) {
				case 'price':
					$currency_position  = give_get_option( 'currency_position', 'before' );
					$price_field_labels = 'minimum' === $amount_range ? __( 'Minimum amount', 'give' ) : __( 'Maximum amount', 'give' );

					$tooltip_html = [
						'before' => Give()->tooltips->render_span(
							[
								'label'       => $price_field_labels,
								'tag_content' => sprintf( '<span class="give-money-symbol give-money-symbol-before">%s</span>', give_currency_symbol() ),
							]
						),
						'after'  => Give()->tooltips->render_span(
							[
								'label'       => $price_field_labels,
								'tag_content' => sprintf( '<span class="give-money-symbol give-money-symbol-after">%s</span>', give_currency_symbol() ),
							]
						),
					];

					$before_html = ! empty( $field_options['before_field'] )
						? $field_options['before_field']
						: ( 'before' === $currency_position ? $tooltip_html['before'] : '' );

					$after_html = ! empty( $field_options['after_field'] )
						? $field_options['after_field']
						: ( 'after' === $currency_position ? $tooltip_html['after'] : '' );

					$field_options['attributes']['class']   .= ' give-text_small';
					$field_options['value'][ $amount_range ] = $amount_value;
					break;

				case 'decimal':
					$field_options['attributes']['class']   .= ' give_input_decimal give-text_small';
					$field_options['value'][ $amount_range ] = $amount_value;
					break;
			}

			echo '<span class=give-minmax-wrap>';
			printf( '<label for="%1$s_give_donation_limit_%2$s">%3$s</label>', esc_attr( $field_options['id'] ), esc_attr( $amount_range ), esc_html( $price_field_labels ) );

			echo isset( $before_html ) ? $before_html : '';
			?>
			<input
					name="<?php echo give_get_field_name( $field_options ); ?>[<?php echo esc_attr( $amount_range ); ?>]"
					type="text"
					id="<?php echo $field_options['id']; ?>_give_donation_limit_<?php echo $amount_range; ?>"
					data-range_type="<?php echo esc_attr( $amount_range ); ?>"
					value="<?php echo give_format_decimal( esc_attr( $field_options['value'][ $amount_range ] ) ); ?>"
					placeholder="<?php echo give_format_decimal( $field_options['options'][ $amount_range ] ); ?>"
				<?php echo give_get_attribute_str( $field_options ); ?>
			/>
			<?php
			echo isset( $after_html ) ? $after_html : '';
			echo '</span>';
		}
		?>
	</span>
		<?php echo give_get_field_description( $field_options ); ?>
	</p>
	<?php
}

/**
 * Output a hidden input box.
 *
 * @since  1.8
 *
 * @param  array $field      {
 *                           Optional. Array of hidden text input field arguments.
 *
 * @type string  $id         Field ID. Default ''.
 * @type string  $value      Value of input field. Default ''.
 * @type string  $name       Name of input field. Default ''.
 * @type string  $type       Type of input field. Default 'text'.
 * @type array   $attributes List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * }
 * @return void
 */
function give_hidden_input( $field ) {
	global $thepostid, $post;

	$thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Custom attribute handling
	$custom_attributes = [];

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

		foreach ( $field['attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}
	?>

	<input
			type="hidden"
			name="<?php echo give_get_field_name( $field ); ?>"
			id="<?php echo esc_attr( $field['id'] ); ?>"
			value="<?php echo esc_attr( $field['value'] ); ?>"
		<?php echo give_get_attribute_str( $field ); ?>
	/>
	<?php
}

/**
 * Output a textarea input box.
 *
 * @since  1.8
 * @since  1.8
 *
 * @param  array $field         {
 *                              Optional. Array of textarea input field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * }
 * @return void
 */
function give_textarea_input( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$default_attributes     = [
		'cols' => 20,
		'rows' => 10,
	];
	?>
	<div class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
		<textarea
				style="<?php echo esc_attr( $field['style'] ); ?>"
				name="<?php echo give_get_field_name( $field ); ?>"
				id="<?php echo esc_attr( $field['id'] ); ?>"
			<?php echo give_get_attribute_str( $field, $default_attributes ); ?>
		><?php echo esc_textarea( $field['value'] ); ?></textarea>
		<?php
		echo give_get_field_description( $field );
		echo '</div>';
}

/**
 * Output a wysiwyg.
 *
 * @since  1.8
 *
 * @param  array $field         {
 *                              Optional. Array of WordPress editor field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * }
 * @return void
 */
function give_wysiwyg( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';

	// Since WP 3.9.0 WP does not allow square brackets in field id.
	// If we pass square brackets in field id then code will work as expected but you will get PHP warnings.
	// wp-includes/class-wp-editor.php::parse_settings::106
	$field['unique_field_id'] = str_replace( [ '[', ']' ], [ '_', '' ], give_get_field_name( $field ) );

	$editor_attributes = [
		'textarea_name' => isset( $field['repeatable_field_id'] ) ? $field['repeatable_field_id'] : $field['id'],
		'textarea_rows' => '10',
		'editor_css'    => esc_attr( $field['style'] ),
		'editor_class'  => $field['attributes']['class'],
	];
	$data_wp_editor    = ' data-wp-editor="' . base64_encode(
		json_encode(
			[
				$field['value'],
				$field['unique_field_id'],
				$editor_attributes,
			]
		)
	) . '"';
	$data_wp_editor    = isset( $field['repeatable_field_id'] ) ? $data_wp_editor : '';

	echo '<div class="give-field-wrap ' . $field['unique_field_id'] . '_field ' . esc_attr( $field['wrapper_class'] ) . '"' . $data_wp_editor . '><label for="' . $field['unique_field_id'] . '">' . wp_kses_post( $field['name'] ) . '</label>';

	wp_editor(
		$field['value'],
		$field['unique_field_id'],
		$editor_attributes
	);

	echo give_get_field_description( $field );
	echo '</div>';
}

/**
 * Output a checkbox input box.
 *
 * @since  1.8
 *
 * @param  array $field         {
 *                              Optional. Array of checkbox field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $cbvalue       Checkbox value. Default 'on'.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
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
	?>
	<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
	<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
	<input
			type="checkbox"
			style="<?php echo esc_attr( $field['style'] ); ?>"
			name="<?php echo give_get_field_name( $field ); ?>"
			id="<?php echo esc_attr( $field['id'] ); ?>"
			value="<?php echo esc_attr( $field['cbvalue'] ); ?>"
		<?php echo checked( $field['value'], $field['cbvalue'], false ); ?>
		<?php echo give_get_attribute_str( $field ); ?>
	/>
	<?php
	echo give_get_field_description( $field );
	echo '</p>';
}

/**
 * Output multi checkbox input box.
 *
 * @since  2.9.0
 *
 * @param  array $field         {
 *                              Optional. Array of checkbox field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * @type array   $options       List of options. Default array().
 *                                               for example: 'options' => array( 'option1' => 'Option 1', 'option2' =>
 *                                               'Option 2' )
 * }
 * @return void
 */
function give_multicheck( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['value']         = is_array( $field['value'] ) ? $field['value'] : [];
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	?>
	<fieldset class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<span class="give-field-label"><?php echo wp_kses_post( $field['name'] ); ?></span>
		<legend class="screen-reader-text"><?php echo wp_kses_post( $field['name'] ); ?></legend>

		<ul>
		<?php foreach ( $field['options'] as $key => $value ) : ?>
			<li>
				<label>
					<input
						type="checkbox"
						name="<?php echo give_get_field_name( $field ); ?>[]"
						value="<?php echo esc_attr( $key ); ?>"
						style="<?php echo esc_attr( $field['style'] ); ?>"
						<?php echo give_get_attribute_str( $field ); ?>
						<?php
						if ( in_array( $key, $field['value'] ) ) {
							echo 'checked="checked"';}
						?>
						/> <?php echo esc_html( $value ); ?>
				</label>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php echo give_get_field_description( $field ); ?>
	</fieldset>
	<?php
}

/**
 * Output a select input box.
 *
 * @since  1.8
 *
 * @param  array $field         {
 *                              Optional. Array of select field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * @type array   $options       List of options. Default array().
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
	?>
	<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
	<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
	<select
	id="<?php echo esc_attr( $field['id'] ); ?>"
	name="<?php echo give_get_field_name( $field ); ?>"
	style="<?php echo esc_attr( $field['style'] ); ?>"
	<?php echo give_get_attribute_str( $field ); ?>
	>
	<?php
	foreach ( $field['options'] as $key => $value ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
	}
	echo '</select>';
	echo give_get_field_description( $field );
	echo '</p>';
}

/**
 * Output a radio input box.
 *
 * @since  1.8
 *
 * @param  array $field         {
 *                              Optional. Array of radio field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * @type array   $options       List of options. Default array().
 *                                               for example: 'options' => array( 'enable' => 'Enable', 'disable' =>
 *                                               'Disable' )
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

	echo '<fieldset class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><span class="give-field-label">' . wp_kses_post( $field['name'] ) . '</span><legend class="screen-reader-text">' . wp_kses_post( $field['name'] ) . '</legend><ul class="give-radios">';

	foreach ( $field['options'] as $key => $value ) {

		echo '<li><label><input
				name="' . give_get_field_name( $field ) . '"
				value="' . esc_attr( $key ) . '"
				type="radio"
				style="' . esc_attr( $field['style'] ) . '"
				' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . ' '
			 . give_get_attribute_str( $field ) . '
				/> ' . esc_html( $value ) . '</label>
		</li>';
	}
	echo '</ul>';

	echo give_get_field_description( $field );
	echo '</fieldset>';
}

/**
 * Output a multi-line radio input box.
 *
 * @since  2.9.0
 *
 * @param  array $field         {
 *                              Optional. Array of radio field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * @type array   $options       List of options. Default array().
 *                                               for example: 'options' => array( 'enable' => [ 'label' => 'Enable', 'description' => 'Description' ] )
 * }
 * @return void
 */
function give_multiradio( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	?>

	<fieldset class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<span class="give-field-label"><?php echo wp_kses_post( $field['name'] ); ?></span>
		<legend class="screen-reader-text"><?php echo wp_kses_post( $field['name'] ); ?></legend>


		<ul class="give-radios">
		<?php foreach ( $field['options'] as $key => $data ) : ?>
			<li>
				<label>
					<input
						type="radio"
						name="<?php echo give_get_field_name( $field ); ?>"
						value="<?php echo esc_attr( $key ); ?>"
						style="<?php echo esc_attr( $field['style'] ); ?>"
						<?php echo checked( esc_attr( $field['value'] ), esc_attr( $key ), false ); ?>
						<?php echo give_get_attribute_str( $field ); ?>
						/> <?php echo esc_html( $data['label'] ); ?>
				</label>
				<?php if ( isset( $data['description'] ) ) : ?>
					<span class="give-field-description">
						<?php echo esc_html( $data['description'] ); ?>
					</span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php echo give_get_field_description( $field ); ?>
	</fieldset>
	<?php
}

/**
 * Output a colorpicker.
 *
 * @since  1.8
 *
 * @param  array $field         {
 *                              Optional. Array of colorpicker field arguments.
 *
 * @type string  $id            Field ID. Default ''.
 * @type string  $style         CSS style for input field. Default ''.
 * @type string  $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string  $value         Value of input field. Default ''.
 * @type string  $name          Name of input field. Default ''.
 * @type string  $description   Description of input field. Default ''.
 * @type array   $attributes    List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
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
	?>
	<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
	<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
	<input
		type="<?php echo esc_attr( $field['type'] ); ?>"
		style="<?php echo esc_attr( $field['style'] ); ?>"
		name="<?php echo give_get_field_name( $field ); ?>"
		id="<?php echo esc_attr( $field['id'] ); ?>"
		value="<?php echo esc_attr( $field['value'] ); ?>"
		<?php echo give_get_attribute_str( $field ); ?>
	/>
	<?php
	echo give_get_field_description( $field );
	echo '</p>';
}

/**
 * Output a file upload field.
 *
 * @since  1.8.9
 *
 * @param array $field
 */
function give_file( $field ) {
	give_media( $field );
}


/**
 * Output a media upload field.
 *
 * @since  1.8
 *
 * @param array $field
 */
function give_media( $field ) {
	global $thepostid, $post;

	$thepostid    = empty( $thepostid ) ? $post->ID : $thepostid;
	$button_label = sprintf( __( 'Add or Upload %s', 'give' ), ( 'file' === $field['type'] ? __( 'File', 'give' ) : __( 'Image', 'give' ) ) );

	$field['style']               = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class']       = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']               = give_get_field_value( $field, $thepostid );
	$field['name']                = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['attributes']['class'] = "{$field['attributes']['class']} give-text-medium";

	// Allow developer to save attachment ID or attachment url as metadata.
	$field['fvalue'] = isset( $field['fvalue'] ) ? $field['fvalue'] : 'url';

	$allow_media_preview_tags = [ 'jpg', 'jpeg', 'png', 'gif', 'ico' ];
	$preview_image_src        = $field['value'] ? ( 'id' === $field['fvalue'] ? wp_get_attachment_url( $field['value'] ) : $field['value'] ) : '#';
	$preview_image_extension  = $preview_image_src ? pathinfo( $preview_image_src, PATHINFO_EXTENSION ) : '';
	$is_show_preview          = in_array( $preview_image_extension, $allow_media_preview_tags );
	?>
	<fieldset class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
		<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
		<input
				name="<?php echo give_get_field_name( $field ); ?>"
				id="<?php echo esc_attr( $field['id'] ); ?>"
				type="text"
				value="<?php echo $field['value']; ?>"
				style="<?php echo esc_attr( $field['style'] ); ?>"
			<?php echo give_get_attribute_str( $field ); ?>
		/>&nbsp;&nbsp;&nbsp;&nbsp;<input class="give-upload-button button" type="button" value="<?php echo $button_label; ?>" data-fvalue="<?php echo $field['fvalue']; ?>" data-field-type="<?php echo $field['type']; ?>">
		<?php echo give_get_field_description( $field ); ?>
		<div class="give-image-thumb<?php echo ! $field['value'] || ! $is_show_preview ? ' give-hidden' : ''; ?>">
			<span class="give-delete-image-thumb dashicons dashicons-no-alt"></span>
			<img src="<?php echo $preview_image_src; ?>" alt="">
		</div>
	</fieldset>
	<?php
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
	$gateways         = give_get_enabled_payment_gateways( $thepostid );
	$field['options'] = [];

	// Set field option value.
	if ( ! empty( $gateways ) ) {
		foreach ( $gateways as $key => $option ) {
			$field['options'][ $key ] = $option['admin_label'];
		}
	}

	// Add a field to the Give Form admin single post view of this field
	if ( is_object( $post ) && 'give_forms' === $post->post_type ) {
		$field['options'] = array_merge( [ 'global' => esc_html__( 'Global Default', 'give' ) ], $field['options'] );
	}

	// Render select field.
	give_select( $field );
}

/**
 * Output the documentation link.
 *
 * @since  1.8
 *
 * @param  array $field      {
 *                           Optional. Array of customizable link attributes.
 *
 * @type string  $name       Name of input field. Default ''.
 * @type string  $type       Type of input field. Default 'text'.
 * @type string  $url        Value to be passed as a link. Default 'https://givewp.com/documentation'.
 * @type string  $title      Value to be passed as text of link. Default 'Documentation'.
 * @type array   $attributes List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * }
 * @return void
 */

function give_docs_link( $field ) {
	$field['url']   = isset( $field['url'] ) ? $field['url'] : 'https://givewp.com/documentation';
	$field['title'] = isset( $field['title'] ) ? $field['title'] : 'Documentation';

	echo '<p class="give-docs-link"><a href="' . esc_url( $field['url'] )
		 . '" target="_blank">'
		 . sprintf( esc_html__( 'Need Help? See docs on "%s"', 'give' ), $field['title'] )
		 . '<span class="dashicons dashicons-editor-help"></span></a></p>';
}


/**
 * Output preview buttons.
 *
 * @since 2.0
 *
 * @param $field
 */
function give_email_preview_buttons( $field ) {
	/* @var WP_Post $post */
	global $post;

	$field_id = str_replace( [ '_give_', '_preview_buttons' ], '', $field['id'] );

	ob_start();

	echo '<p class="give-field-wrap ' . esc_attr( $field['id'] ) . '_field"><label for="' . give_get_field_name( $field ) . '">' . wp_kses_post( $field['name'] ) . '</label>';

	echo sprintf(
		'<a href="%1$s" class="button-secondary" target="_blank">%2$s</a>',
		wp_nonce_url(
			add_query_arg(
				[
					'give_action' => 'preview_email',
					'email_type'  => $field_id,
					'form_id'     => $post->ID,
				],
				home_url()
			),
			'give-preview-email'
		),
		$field['name']
	);

	echo sprintf(
		' <a href="%1$s" aria-label="%2$s" class="button-secondary">%3$s</a>',
		wp_nonce_url(
			add_query_arg(
				[
					'give_action'     => 'send_preview_email',
					'email_type'      => $field_id,
					'give-messages[]' => 'sent-test-email',
					'form_id'         => $post->ID,
				]
			),
			'give-send-preview-email'
		),
		esc_attr__( 'Send Test Email.', 'give' ),
		esc_html__( 'Send Test Email', 'give' )
	);

	if ( ! empty( $field['description'] ) ) {
		echo '<span class="give-field-description">' . wp_kses_post( $field['desc'] ) . '</span>';
	}

	echo '</p>';

	echo ob_get_clean();
}

/**
 * Get setting field value.
 *
 * Note: Use only for single post, page or custom post type.
 *
 * @since  1.8
 * @since  2.1 Added support for donation_limit.
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

	// If field is range slider.
	if ( 'donation_limit' === $field['type'] ) {

		// Get minimum value.
		$minimum = give_get_meta( $postid, $field['id'] . '_minimum', true );

		// Give < 2.1
		if ( '_give_custom_amount_range' === $field['id'] && empty( $minimum ) ) {
			$minimum = give_get_meta( $postid, '_give_custom_amount_minimum', true );
		}

		$field_value = [
			'minimum' => $minimum,
			'maximum' => give_get_meta( $postid, $field['id'] . '_maximum', true ),
		];
	} else {
		// Get value from db.
		$field_value = give_get_meta( $postid, $field['id'], true );
	}

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
 * Get field description html.
 *
 * @since 1.8
 *
 * @param $field
 *
 * @return string
 */
function give_get_field_description( $field ) {
	$field_desc_html = '';
	$description     = '';

	// Check for both `description` and `desc`.
	if ( isset( $field['description'] ) ) {
		$description = $field['description'];
	} elseif ( isset( $field['desc'] ) ) {
		$description = $field['desc'];
	}

	// Set if there is a description.
	if ( ! empty( $description ) ) {
		$field_desc_html = '<span class="give-field-description">' . wp_kses_post( $description ) . '</span>';
	}

	return $field_desc_html;
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
	$field_name = esc_attr( empty( $field['repeat'] ) ? $field['id'] : $field['repeatable_field_id'] );

	/**
	 * Filter the field name.
	 *
	 * @since 1.8
	 *
	 * @param string $field_name
	 */
	$field_name = apply_filters( 'give_get_field_name', $field_name, $field );

	return $field_name;
}

/**
 * Output repeater field or multi donation type form on donation from edit screen.
 * Note: internal use only.
 *
 * @TODO   : Add support for wysiwyg type field.
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
	$close_tabs      = isset( $fields['options']['close_tabs'] ) ? (int) $fields['options']['close_tabs'] : 0;
	$wrapper_class   = isset( $fields['wrapper_class'] ) ? $fields['wrapper_class'] : '';
	?>
	<div class="give-repeatable-field-section <?php echo esc_attr( $wrapper_class ); ?>" id="<?php echo "{$fields['id']}_field"; ?>"
		 data-group-numbering="<?php echo $group_numbering; ?>" data-close-tabs="<?php echo $close_tabs; ?>">
		<?php if ( ! empty( $fields['name'] ) ) : ?>
			<p class="give-repeater-field-name"><?php echo $fields['name']; ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $fields['description'] ) ) : ?>
			<p class="give-repeater-field-description"><?php echo $fields['description']; ?></p>
		<?php endif; ?>

		<table class="give-repeatable-fields-section-wrapper" cellspacing="0">
			<?php
			// Get value.
			$repeater_field_values = ! empty( $fields['attributes']['value'] )
				? $fields['attributes']['value']
				: give_get_meta( $thepostid, $fields['id'], true );

			// Setup default value.
			if ( empty( $repeater_field_values ) && ! empty( $fields['default'] ) ) {
				$repeater_field_values = $fields['default'];
			}

			$header_title = isset( $fields['options']['header_title'] )
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
						<button type="button" class="give-handlediv button-link"><span class="toggle-indicator"></span>
						</button>
						<span class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-</span>
						<h2>
							<span data-header-title="<?php echo $header_title; ?>"><?php echo $header_title; ?></span>
						</h2>
					</div>
					<div class="give-row-body">
						<?php foreach ( $fields['fields'] as $field ) : ?>
							<?php
							if ( ! give_is_field_callback_exist( $field ) ) {
								continue;
							}
							?>
							<?php
							$field['repeat']              = true;
							$field['repeatable_field_id'] = give_get_repeater_field_id( $field, $fields );
							$field['id']                  = str_replace(
								[ '[', ']' ],
								[ '_', '' ],
								$field['repeatable_field_id']
							);
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
								<button type="button" class="give-handlediv button-link">
									<span class="toggle-indicator"></span></button>
								<span class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-
								</span>
								<h2>
									<span data-header-title="<?php echo $header_title; ?>"><?php echo $header_title; ?></span>
								</h2>
							</div>
							<div class="give-row-body">
								<?php foreach ( $fields['fields'] as $field ) : ?>
									<?php
									if ( ! give_is_field_callback_exist( $field ) ) {
										continue;
									}
									?>
									<?php
									$field['repeat']              = true;
									$field['repeatable_field_id'] = give_get_repeater_field_id( $field, $fields, $index );
									$field['attributes']['value'] = give_get_repeater_field_value( $field, $field_group, $fields );
									$field['id']                  = str_replace(
										[ '[', ']' ],
										[ '_', '' ],
										$field['repeatable_field_id']
									);
									?>
									<?php give_render_field( $field ); ?>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
					<?php
				endforeach;
				?>

			<?php elseif ( $add_default_donation_field ) : ?>
				<!--Default repeater field group-->
				<tr class="give-row">
					<td class="give-repeater-field-wrap give-column" colspan="2">
						<div class="give-row-head give-move">
							<button type="button" class="give-handlediv button-link">
								<span class="toggle-indicator"></span></button>
							<span class="give-remove" title="<?php esc_html_e( 'Remove Group', 'give' ); ?>">-
							</span>
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
								$field['repeatable_field_id'] = give_get_repeater_field_id( $field, $fields, 0 );
								$field['attributes']['value'] = apply_filters(
									"give_default_field_group_field_{$field['id']}_value",
									( ! empty( $field['default'] ) ? $field['default'] : '' ),
									$field,
									$fields
								);
								$field['id']                  = str_replace(
									[ '[', ']' ],
									[ '_', '' ],
									$field['repeatable_field_id']
								);
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
				$add_row_btn_title       = isset( $fields['options']['add_button'] )
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
	$row_placeholder = false !== $default ? $default : '{{row-count-placeholder}}';
	$field_id        = "{$fields['id']}[{$row_placeholder}][{$field['id']}][level_id]";

	return $field_id;
}

add_filter( 'give_get_repeater_field__give_id_id', '_give_set_multi_level_repeater_field_id', 10, 4 );

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
	$field_value = $field_group[ $field['id'] ]['level_id'];

	return $field_value;
}

add_filter( 'give_get_repeater_field__give_id_value', '_give_set_multi_level_repeater_field_value', 10, 4 );

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

/**
 * Set repeater field editor id for field type wysiwyg.
 *
 * @since 1.8
 *
 * @param $field_name
 * @param $field
 *
 * @return string
 */
function give_repeater_field_set_editor_id( $field_name, $field ) {
	if ( isset( $field['repeatable_field_id'] ) && 'wysiwyg' == $field['type'] ) {
		$field_name = '_give_repeater_' . uniqid() . '_wysiwyg';
	}

	return $field_name;
}

add_filter( 'give_get_field_name', 'give_repeater_field_set_editor_id', 10, 2 );

/**
 * Output Donation form radio input box.
 *
 * @since  2.1.3
 *
 * @param  array $field {
 *                              Optional. Array of radio field arguments.
 *
 * @type string $id Field ID. Default ''.
 * @type string $style CSS style for input field. Default ''.
 * @type string $wrapper_class CSS class to use for wrapper of input field. Default ''.
 * @type string $value Value of input field. Default ''.
 * @type string $name Name of input field. Default ''.
 * @type string $description Description of input field. Default ''.
 * @type array $attributes List of attributes of input field. Default array().
 *                                               for example: 'attributes' => array( 'placeholder' => '*****', 'class'
 *                                               => '****' )
 * @type array $options List of options. Default array().
 *                                               for example: 'options' => array( 'enable' => 'Enable', 'disable' =>
 *                                               'Disable' )
 * }
 * @return void
 */
function give_donation_form_goal( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = give_get_field_value( $field, $thepostid );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	printf(
		'<fieldset class="give-field-wrap %s_field %s">',
		esc_attr( $field['id'] ),
		esc_attr( $field['wrapper_class'] )
	);

	printf(
		'<span class="give-field-label">%s</span>',
		esc_html( $field['name'] )
	);

	printf(
		'<legend class="screen-reader-text">%s</legend>',
		esc_html( $field['name'] )
	);
	?>

	<ul class="give-radios">
		<?php
		foreach ( $field['options'] as $key => $value ) {
			$attributes = empty( $field['attributes'] ) ? '' : give_get_attribute_str( $field['attributes'] );
			printf(
				'<li><label><input name="%s" value="%s" type="radio" style="%s" %s %s /> %s </label></li>',
				give_get_field_name( $field ),
				esc_attr( $key ),
				esc_attr( $field['style'] ),
				checked( esc_attr( $field['value'] ), esc_attr( $key ), false ),
				$attributes,
				esc_html( $value )
			);
		}
		?>
	</ul>

	<?php
	/**
	 * Action to add HTML after donation form radio button is display and before description.
	 *
	 * @since 2.1.3
	 *
	 * @param array $field Array of radio field arguments.
	 */
	do_action( 'give_donation_form_goal_before_description', $field );

	echo give_get_field_description( $field );

	echo '</fieldset>';
}
