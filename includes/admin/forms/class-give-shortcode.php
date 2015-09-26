<?php
/**
 * Give Shortcode
 *
 * @description: Adds the ability for users to add Give forms to Tiny MCE and across the site
 * @license    : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.0.0
 * @created    : 26/09/2015
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

abstract class Give_Shortcode {

	/**
	 * The shortcode tag
	 */
	public $shortcode;

	/**
	 * The shortcode fields array
	 */
	public $fields;

	/**
	 * The shortcode dialog text variables
	 */
	public $dialog_title;
	public $dialog_alert;
	public $dialog_okay;
	public $dialog_close;

	/**
	 * Class constructor
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_head',            array( $this, 'admin_head' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'media_buttons',         array( $this, 'give_shortcode_button' ), 11 );
		}

		add_action( "wp_ajax_{$this->shortcode}_ajax",        array( $this, 'give_shortcode_ajax' ) );
		add_action( "wp_ajax_nopriv_{$this->shortcode}_ajax", array( $this, 'give_shortcode_ajax' ) );
	}

	/**
	 * Define the shortcode dialog fields
	 *
	 * @return array
	 */
	public abstract function define_fields();

	/**
	 * Trigger custom admin_head hooks
	 *
	 * @return void
	 */
	public function admin_head() {

		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {

			add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
		}
	}

	/**
	 * Register the TinyMCE plugin
	 *
	 * @param array $plugin_array
	 *
	 * @return array
	 */
	public function mce_external_plugins( $plugin_array ) {

		$plugin = 'assets/js/admin/tinymce/mce-' . $this->shortcode . '.js';

		if ( file_exists( GIVE_PLUGIN_DIR . $plugin ) ) {
			$plugin_array[ $this->shortcode ] = GIVE_PLUGIN_URL . $plugin;
		}

		return $plugin_array;
	}

	/**
	 * Enqueue the admin scripts
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		wp_enqueue_script(
			'give_form_shortcode',
			GIVE_PLUGIN_URL . 'assets/js/admin/admin-shortcodes.js',
			array( 'jquery' ),
			GIVE_VERSION,
			true
		);
	}

	/**
	 * Adds the "Donation Form" button above the TinyMCE Editor on add/edit screens.
	 *
	 * @return string
	 */
	public abstract function give_shortcode_button();

	/**
	 * Load the shortcode dialog fields via AJAX
	 *
	 * @return void
	 */
	public function give_shortcode_ajax() {

		wp_send_json( array(
			'shortcode' => $this->shortcode,
			'body'      => $this->generate_fields(),
			'title'     => $this->dialog_title,
			'alert'     => $this->dialog_alert,
			'ok'        => $this->dialog_okay,
			'close'     => $this->dialog_close,
		) );
	}

	/**
	 * Generate the shortcode dialog fields
	 *
	 * @return array
	 */
	protected function generate_fields() {

		$fields = array();

		if ( ! $this->fields ) {
			$this->fields = $this->define_fields();
		}

		foreach ( $this->fields as $field ) {

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

				$field = call_user_func( array( get_class( $this ), $method ), $field );

				if ( $field ) {
					$fields[] = $field;
				}
			}
		}

		return $fields;
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
			'label'   => '',
			'name'    => false,
			'tooltip' => '',
			'type'    => '',
			'value'   => '',
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
}
