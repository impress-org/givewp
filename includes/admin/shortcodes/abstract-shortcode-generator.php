<?php
/**
 * Shortcode Dialog Generator abstract class
 *
 * @package     Give/Admin
 * @author      Paul Ryley
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @version     1.0
 * @since       1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Shortcode_Generator
 */
abstract class Give_Shortcode_Generator {

	/**
	 * The current class name
	 *
	 * @since 1.0
	 */
	public $self;

	/**
	 * The current shortcode
	 *
	 * @since 1.0
	 */
	public $shortcode;

	/**
	 * The current shortcode tag
	 *
	 * @since 1.0
	 */
	public $shortcode_tag;

	/**
	 * Shortcode field errors
	 *
	 * @since 1.0
	 */
	protected $errors;

	/**
	 * Required shortcode fields
	 *
	 * @since 1.0
	 */
	protected $required;

	/**
	 * Class constructor
	 *
	 * @param string $shortcode The shortcode tag
	 *
	 * @since 1.0
	 */
	public function __construct( $shortcode ) {

		$this->shortcode_tag = $shortcode;

		add_action( 'admin_init', array( $this, 'init' ) );

	}

	/**
	 * Kick things off for the shortcode generator
	 *
	 * @since 1.3.0.2
	 */
	public function init() {

		if ( $this->shortcode_tag ) {

			$this->self = get_class( $this );

			$this->errors   = array();
			$this->required = array();

			// Generate the fields, errors, and requirements
			$fields = $this->get_fields();

			$defaults = array(
				'btn_close' => esc_html__( 'Close', 'give' ),
				'btn_okay'  => esc_html__( 'Insert Shortcode', 'give' ),
				'errors'    => $this->errors,
				'fields'    => $fields,
				'label'     => '[' . $this->shortcode_tag . ']',
				'required'  => $this->required,
				'title'     => esc_html__( 'Insert Shortcode', 'give' ),
			);

			if ( user_can_richedit() ) {

				Give_Shortcode_Button::$shortcodes[ $this->shortcode_tag ] = wp_parse_args( $this->shortcode, $defaults );

			}
		}

	}


	/**
	 * Define the shortcode attribute fields
	 *
	 * @return false|array
	 *
	 * @since 1.0
	 */
	public function define_fields() {

		return false;
	}

	/**
	 * Generate the shortcode dialog fields
	 *
	 * @param array $defined_fields
	 *
	 * @return array
	 *
	 * @since 1.0
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
	 * Get the generated shortcode dialog fields
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function get_fields() {

		$defined_fields   = $this->define_fields();
		$generated_fields = $this->generate_fields( $defined_fields );

		$errors = array();

		if ( ! empty( $this->errors ) ) {
			foreach ( $this->required as $name => $alert ) {
				// Using WordPress function in place of array_column wp_list_pluck as it support older version as well.
				if ( false === array_search( $name, give_list_pluck( $generated_fields, 'name' ) ) ) {

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
	 * Generate a TinyMCE docs_link field
	 *
	 * @param $field
	 *
	 * @return array
	 */
	protected function generate_docs_link( $field ){
		// Add custom style to override mce style.
		$dashicon_style = 'width: 20px;
			height: 20px;
			font-size: 17px;
			line-height: 1;
			font-family: dashicons;
			text-decoration: inherit;
			font-weight: normal;
			font-style: normal;
			vertical-align: top;
			text-align: center;
			transition: color .1s ease-in 0;';

		$a_style = 'color: #999;
			text-decoration: none;
			font-style: italic;
			font-size: 13px;';

		$p_style = 'text-align:right;';

		return $this->generate_container(
			array(
				'type' => 'container',
				'html' => sprintf(
					'<p class="give-docs-link" style="%5$s"><a href="%4$s" style="%3$s" target="_blank">%1$s<span class="dashicons dashicons-editor-help" style="%2$s"></a></span></p>',
					$field['text'],
					$dashicon_style,
					$a_style,
					esc_url( $field['link'] ),
					$p_style
				)
			)
		);
	}

	/**
	 * Generate a TinyMCE container field
	 *
	 * @param array $field
	 *
	 * @return array|false
	 *
	 * @since 1.0
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
	 *
	 * @since 1.0
	 */
	protected function generate_listbox( $field ) {

		$listbox = shortcode_atts( array(
			'label'    => '',
			'minWidth' => '',
			'name'     => false,
			'tooltip'  => '',
			'type'     => '',
			'value'    => '',
			'classes'  => ''
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
				                    '' => ( $field['placeholder'] ? $field['placeholder'] : esc_attr__( '- Select -', 'give' ) ),
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
	 * Generate a TinyMCE listbox field for a post_type.
	 *
	 * @param array $field
	 *
	 * @return array|false
	 *
	 * @since 1.0
	 */
	protected function generate_post( $field ) {

		$args = array(
			'post_type'        => 'post',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'posts_per_page'   => 30,
			'suppress_filters' => false,
		);

		$args    = wp_parse_args( (array) $field['query_args'], $args );

		$posts   = get_posts( $args );
		$options = array();

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[ absint( $post->ID ) ] = empty( $post->post_title )
					? sprintf( __( 'Untitled (#%s)', 'give' ), $post->ID )
					/** This filter is documented in wp-includes/post-template.php */
					: apply_filters( 'the_title', $post->post_title, $post->ID );
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
	 *
	 * @since 1.0
	 */
	protected function generate_textbox( $field ) {

		$textbox = shortcode_atts( array(
			'label'       => '',
			'maxLength'   => '',
			'minHeight'   => '',
			'minWidth'    => '',
			'multiline'   => false,
			'name'        => false,
			'tooltip'     => '',
			'type'        => '',
			'value'       => '',
			'classes'     => '',
			'placeholder' => ''
		), $field );

		// Remove empty placeholder.
		if( empty( $textbox['placeholder'] ) ) {
			unset( $textbox['placeholder'] );
		}

		if ( $this->validate( $field ) ) {
			return array_filter( $textbox, array( $this, 'return_textbox_value' ) );
		}

		return false;
	}

	/**
	 * Validate Textbox Value
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	function return_textbox_value( $value ) {
		return $value !== '';
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
	 *
	 * @since 1.0
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

			if ( ! ! $required || is_array( $required ) ) {

				$alert = esc_html__( 'Some of the shortcode options are required.', 'give' );

				if ( isset( $required['alert'] ) ) {

					$alert = $required['alert'];

				} else if ( ! empty( $label ) ) {

					$alert = sprintf(
					/* translators: %s: option label */
						esc_html__( 'The "%s" option is required.', 'give' ),
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
