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
	 * Class constructor
	 *
	 * @param string $shortcode The shortcode tag
	 */
	public function __construct( $shortcode ) {

		if ( $shortcode ) {

			$this->self = get_class( $this );

			$defaults = array(
				'alert'     => __( 'Some of the shortcode fields are required!', 'give' ),
				'btn_close' => __( 'Close', 'give' ),
				'btn_okay'  => __( 'Insert Shortcode', 'give' ),
				'fields'    => $this->generate_fields(),
				'label'     => '[' . $shortcode . ']',
				'require'   => array(),
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
	 * @return array
	 */
	protected function generate_fields() {

		$fields = array();

		$shortcode_fields = $this->define_fields();

		if ( $shortcode_fields ) {

			foreach ( $shortcode_fields as $field ) {

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

		if ( $listbox['name'] ) {

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

		// @todo: else if nothing found...

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

		if ( $textbox['name'] ) {

			return array_filter( $textbox, function( $value ) { return $value !== ''; } );
		}

		return false;
	}
}
