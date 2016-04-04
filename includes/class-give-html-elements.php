<?php
/**
 * HTML elements
 *
 * A helper class for outputting common HTML elements, such as product drop downs
 *
 * @package     Give
 * @subpackage  Classes/HTML
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_HTML_Elements Class
 *
 * @since 1.0
 */
class Give_HTML_Elements {

	/**
	 * Renders an HTML Dropdown of all the Give Forms
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $args Arguments for the dropdown
	 *
	 * @return string $output Give forms dropdown
	 */
	public function forms_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'forms',
			'id'          => 'forms',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => false,
			'number'      => 30,
			'placeholder' => sprintf( __( 'Select a %s', 'give' ), give_get_forms_label_singular() )
		);

		$args = wp_parse_args( $args, $defaults );

		$forms = get_posts( array(
			'post_type'      => 'give_forms',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'posts_per_page' => $args['number']
		) );

		$options = array();

		if ( $forms ) {
			$options[0] = sprintf( __( 'Select a %s', 'give' ), give_get_forms_label_singular() );
			foreach ( $forms as $form ) {
				$options[ absint( $form->ID ) ] = esc_html( $form->post_title );
			}
		} else {
			$options[0] = __( 'No Give Forms Found', 'give' );
		}

		// This ensures that any selected forms are included in the drop down
		if ( is_array( $args['selected'] ) ) {
			foreach ( $args['selected'] as $item ) {
				if ( ! in_array( $item, $options ) ) {
					$options[ $item ] = get_the_title( $item );
				}
			}
		} elseif ( is_numeric( $args['selected'] ) && $args['selected'] !== 0 ) {
			if ( ! in_array( $args['selected'], $options ) ) {
				$options[ $args['selected'] ] = get_the_title( $args['selected'] );
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'],
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all customers
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $args
	 *
	 * @return string $output Donor dropdown
	 */
	public function donor_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'customers',
			'id'          => 'customers',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => true,
			'placeholder' => __( 'Select a Donor', 'give' ),
			'number'      => 30
		);

		$args = wp_parse_args( $args, $defaults );

		$customers = Give()->customers->get_customers( array(
			'number' => $args['number']
		) );

		$options = array();

		if ( $customers ) {
			$options[0] = __( 'No donor attached', 'give' );
			foreach ( $customers as $customer ) {
				$options[ absint( $customer->id ) ] = esc_html( $customer->name . ' (' . $customer->email . ')' );
			}
		} else {
			$options[0] = __( 'No donors found', 'give' );
		}

		if ( ! empty( $args['selected'] ) ) {

			// If a selected customer has been specified, we need to ensure it's in the initial list of customers displayed

			if ( ! array_key_exists( $args['selected'], $options ) ) {

				$customer = new Give_Customer( $args['selected'] );

				if ( $customer ) {

					$options[ absint( $args['selected'] ) ] = esc_html( $customer->name . ' (' . $customer->email . ')' );

				}

			}

		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' give-customer-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}


	/**
	 * Renders an HTML Dropdown of all the Categories
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $name     Name attribute of the dropdown
	 * @param int    $selected Category to select automatically
	 *
	 * @return string $output Category dropdown
	 */
	public function category_dropdown( $name = 'give_forms_categories', $selected = 0 ) {
		$categories = get_terms( 'give_forms_category', apply_filters( 'give_forms_category_dropdown', array() ) );
		$options    = array();

		foreach ( $categories as $category ) {
			$options[ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => __( 'All Categories', 'give' ),
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of years
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $name         Name attribute of the dropdown
	 * @param int    $selected     Year to select automatically
	 * @param int    $years_before Number of years before the current year the dropdown should start with
	 * @param int    $years_after  Number of years after the current year the dropdown should finish at
	 *
	 * @return string $output Year dropdown
	 */
	public function year_dropdown( $name = 'year', $selected = 0, $years_before = 5, $years_after = 0 ) {
		$current    = date( 'Y' );
		$start_year = $current - absint( $years_before );
		$end_year   = $current + absint( $years_after );
		$selected   = empty( $selected ) ? date( 'Y' ) : $selected;
		$options    = array();

		while ( $start_year <= $end_year ) {
			$options[ absint( $start_year ) ] = $start_year;
			$start_year ++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}


	/**
	 * Renders an HTML Dropdown of months
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $name     Name attribute of the dropdown
	 * @param int    $selected Month to select automatically
	 *
	 * @return string $output Month dropdown
	 */
	public function month_dropdown( $name = 'month', $selected = 0 ) {
		$month    = 1;
		$options  = array();
		$selected = empty( $selected ) ? date( 'n' ) : $selected;

		while ( $month <= 12 ) {
			$options[ absint( $month ) ] = give_month_num_to_name( $month );
			$month ++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function select( $args = array() ) {
		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => 0,
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'give' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'give' )
		);

		$args = wp_parse_args( $args, $defaults );


		if ( $args['multiple'] ) {
			$multiple = ' MULTIPLE';
		} else {
			$multiple = '';
		}

		if ( $args['chosen'] ) {
			$args['class'] .= ' give-select-chosen';
		}

		if ( $args['placeholder'] ) {
			$placeholder = $args['placeholder'];
		} else {
			$placeholder = '';
		}

		$output = '<select name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( sanitize_key( str_replace( '-', '_', $args['id'] ) ) ) . '" class="give-select ' . esc_attr( $args['class'] ) . '"' . $multiple . ' data-placeholder="' . $placeholder . '">';

		if ( $args['show_option_all'] ) {
			if ( $args['multiple'] ) {
				$selected = selected( true, in_array( 0, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], 0, false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
		}

		if ( ! empty( $args['options'] ) ) {

			if ( $args['show_option_none'] ) {
				if ( $args['multiple'] ) {
					$selected = selected( true, in_array( - 1, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], - 1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
			}

			foreach ( $args['options'] as $key => $option ) {

				if ( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( $key, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], $key, false );
				}

				$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Renders an HTML Checkbox
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function checkbox( $args = array() ) {
		$defaults = array(
			'name'    => null,
			'current' => null,
			'class'   => 'give-checkbox',
			'options' => array(
				'disabled' => false,
				'readonly' => false
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$options = '';
		if ( ! empty( $args['options']['disabled'] ) ) {
			$options .= ' disabled="disabled"';
		} elseif ( ! empty( $args['options']['readonly'] ) ) {
			$options .= ' readonly';
		}

		$output = '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" class="' . $args['class'] . ' ' . esc_attr( $args['name'] ) . '" ' . checked( 1, $args['current'], false ) . ' />';

		return $output;
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return string Text field
	 */
	public function text( $args = array() ) {
		// Backwards compatabliity
		if ( func_num_args() > 1 ) {
			$args = func_get_args();

			$name  = $args[0];
			$value = isset( $args[1] ) ? $args[1] : '';
			$label = isset( $args[2] ) ? $args[2] : '';
			$desc  = isset( $args[3] ) ? $args[3] : '';
		}

		$defaults = array(
			'name'         => isset( $name ) ? $name : 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc ) ? $desc : null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => false
		);

		$args = wp_parse_args( $args, $defaults );

		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$data = '';
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . $key . '="' . $value . '" ';
			}
		}

		$output = '<span id="give-' . sanitize_key( $args['name'] ) . '-wrap">';

		$output .= '<label class="give-label" for="give-' . sanitize_key( $args['name'] ) . '">' . esc_html( $args['label'] ) . '</label>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="give-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		$output .= '<input type="text" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" autocomplete="' . esc_attr( $args['autocomplete'] ) . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $args['class'] . '" ' . $data . '' . $disabled . '/>';

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders an HTML textarea
	 *
	 * @since 1.0
	 *
	 * @param array $args Arguments for the textarea
	 *
	 * @return string textarea
	 */
	public function textarea( $args = array() ) {
		$defaults = array(
			'name'     => 'textarea',
			'value'    => null,
			'label'    => null,
			'desc'     => null,
			'class'    => 'large-text',
			'disabled' => false
		);

		$args = wp_parse_args( $args, $defaults );

		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$output = '<span id="give-' . sanitize_key( $args['name'] ) . '-wrap">';

		$output .= '<label class="give-label" for="give-' . sanitize_key( $args['name'] ) . '">' . esc_html( $args['label'] ) . '</label>';

		$output .= '<textarea name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" class="' . $args['class'] . '"' . $disabled . '>' . esc_attr( $args['value'] ) . '</textarea>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="give-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders an ajax user search field
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return string text field with ajax search
	 */
	public function ajax_user_search( $args = array() ) {

		$defaults = array(
			'name'         => 'user_id',
			'value'        => null,
			'placeholder'  => __( 'Enter username', 'give' ),
			'label'        => null,
			'desc'         => null,
			'class'        => '',
			'disabled'     => false,
			'autocomplete' => 'off',
			'data'         => false
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] = 'give-ajax-user-search ' . $args['class'];

		$output = '<span class="give_user_search_wrap">';
		$output .= $this->text( $args );
		$output .= '<span class="give_user_search_results hidden"><a class="give-ajax-user-cancel" title="' . __( 'Cancel', 'give' ) . '" aria-label="' . __( 'Cancel', 'give' ) . '" href="#">x</a><span></span></span>';
		$output .= '</span>';

		return $output;
	}
}
