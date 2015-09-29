<?php
/**
 * Shortcode Dialog Generator abstract class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

defined( 'ABSPATH' ) or exit;

abstract class Give_Shortcode_Generator {

	/**
	 * The current class name
	 */
	public $self;

	/**
	 * The current shortcode
	 */
	public $shortcode;

	/**
	 * Shortcode field errors
	 */
	public $errors;

	/**
	 * Required shortcode fields
	 */
	public $required;

	/**
	 * Class constructor
	 *
	 * @param string $shortcode The shortcode tag
	 */
	public function __construct( $shortcode ) {

		if ( $shortcode ) {

			$this->self = get_class( $this );

			$this->errors   = array();
			$this->required = array();

			// Generate the fields, errors, and requirements
			$fields = $this->fields();

			$defaults = array(
				'btn_close' => __( 'Close', 'give' ),
				'btn_okay'  => __( 'Insert Shortcode', 'give' ),
				'errors'    => $this->errors,
				'fields'    => $fields,
				'label'     => '[' . $shortcode . ']',
				'required'  => $this->required,
				'title'     => __( 'Insert Shortcode', 'give' ),
			);

			Give_Shortcode_Button::$shortcodes[ $shortcode ] = wp_parse_args( $this->shortcode, $defaults );
		}
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return false|array
	 */
	protected function define_fields() {

		return false;
	}

	/**
	 * Generate the shortcode dialog fields
	 *
	 * @param array $defined_fields
	 *
	 * @return array
	 */
	protected function generate_fields( $defined_fields ) {

		$fields = array();

		if ( is_array( $defined_fields ) ) {

			foreach ( $defined_fields as $field ) {

				$defaults = array(
					'label'       => false,
					'name'        => false,
					'options'     => array(),
					'placeholder' => false,
					'tooltip'     => false,
					'type'        => '',
				);

				$field  = wp_parse_args( (array) $field, $defaults );
				$method = 'generate_' . strtolower( $field['type'] );

				if ( method_exists( $this, $method ) ) {

					$field = call_user_func( array( $this, $method ), $field );

					if ( $field ) {
						$fields[] = $field;
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Perform final shortcode error validation
	 *
	 * @param array $defined_fields
	 * @param array $generated_fields
	 *
	 * @return array
	 */
	protected function fields() {

		$defined_fields   = $this->define_fields();
		$generated_fields = $this->generate_fields( $defined_fields );

		$errors = array();

		if ( ! empty( $this->errors ) ) {
			foreach ( $this->required as $name => $alert ) {
				if ( false === array_search( $name, array_column( $generated_fields, 'name' ) ) ) {

					$errors[] = $this->errors[ $name ];
				}
			}

			$this->errors = $errors;
		}

		if ( ! empty( $errors ) ) {

			return $errors;
		}

		return $generated_fields;
	}

	/**
	 * Generate a TinyMCE container field
	 *
	 * @param array $field
	 *
	 * @return array|false
	 */
	protected function generate_container( $field ) {

		if ( array_key_exists( 'html', $field ) ) {

			return array(
				'type' => $field['type'],
				'html' => $field['html'],
			);
		}

		return false;
	}

	/**
	 * Generate a TinyMCE listbox field
	 *
	 * @param array $field
	 *
	 * @return array|false
	 */
	protected function generate_listbox( $field ) {

		$listbox = shortcode_atts( array(
			'label'    => '',
			'minWidth' => '',
			'name'     => false,
			'tooltip'  => '',
			'type'     => '',
			'value'    => '',
		), $field );

		if ( $this->validate( $field ) ) {

			$new_listbox = array();

			foreach ( $listbox as $key => $value ) {

				if ( $key == 'value' && empty( $value ) ) {
					$new_listbox[ $key ] = $listbox['name'];
				} else if ( $value ) {
					$new_listbox[ $key ] = $value;
				}
			}

			// do not reindex array!
			$field['options'] = array(
				''  => ( $field['placeholder'] ? $field['placeholder'] : sprintf( '– %s –', __( 'Select', 'give' ) ) ),
			) + $field['options'];

			foreach ( $field['options'] as $value => $text ) {
				$new_listbox['values'][] = array(
					'text'  => $text,
					'value' => $value,
				);
			}

			return $new_listbox;
		}

		return false;
	}

	/**
	 * Generate a TinyMCE listbox field for a post_type
	 *
	 * @param array $field
	 *
	 * @return array|false
	 */
	protected function generate_post( $field ) {

		$args = array(
			'post_type'      => 'post',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'posts_per_page' => 30,
		);

		$args    = wp_parse_args( (array) $field['query_args'], $args );
		$posts   = get_posts( $args );
		$options = array();

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$options[ absint( $post->ID ) ] = esc_html( $post->post_title );
			}

			$field['type']    = 'listbox';
			$field['options'] = $options;

			return $this->generate_listbox( $field );
		}

		// perform validation here before returning false
		$this->validate( $field );

		return false;
	}

	/**
	 * Generate a TinyMCE textbox field
	 *
	 * @param array $field
	 *
	 * @return array|false
	 */
	protected function generate_textbox( $field ) {

		$textbox = shortcode_atts( array(
			'label'     => '',
			'maxLength' => '',
			'minHeight' => '',
			'minWidth'  => '',
			'multiline' => false,
			'name'      => false,
			'tooltip'   => '',
			'type'      => '',
			'value'     => '',
		), $field );

		if ( $this->validate( $field ) ) {

			return array_filter( $textbox, function( $value ) { return $value !== ''; } );
		}

		return false;
	}

	/**
	 * Perform validation for a single field
	 *
	 * Returns true or false depending on whether the field has a 'name' attribute.
	 * This method also populates the shortcode's $errors and $required arrays.
	 *
	 * @param array $field
	 *
	 * @return bool
	 */
	protected function validate( $field ) {

		extract( shortcode_atts(
			array(
				'name'     => false,
				'required' => false,
				'label'    => '',
			), $field )
		);

		if ( $name ) {

			if ( isset( $required['error'] ) ) {

				$error = array(
					'type' => 'container',
					'html' => $required['error'],
				);

				$this->errors[ $name ] = $this->generate_container( $error );
			}

			if ( !! $required || is_array( $required ) ) {

				$alert = __( 'Some of the Shortcode options are required.', 'give' );

				if ( isset( $required['alert'] ) ) {

					$alert = $required['alert'];

				} else if ( ! empty( $label ) ) {

					$alert = sprintf( __( 'The "%s" option is required.', 'give' ),
						str_replace( ':', '', $label )
					);
				}

				$this->required[ $name ] = $alert;
			}

			return true;
		}

		return false;
	}
}
