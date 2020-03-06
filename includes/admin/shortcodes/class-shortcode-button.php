<?php
/**
 * Shortcode Button Class
 *
 * @package     Give
 * @subpackage  Admin
 * @author      Paul Ryley
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @version     1.0
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Shortcode_Button
 */
final class Give_Shortcode_Button {

	/**
	 * All shortcode tags
	 *
	 * @since 1.0
	 */
	public static $shortcodes;

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'init' ), 999 );
		add_action( 'admin_init', array( $this, 'ajax_handler' ) );
	}

	/**
	 * Initialize
	 *
	 * @since  2.1.0
	 * @access public
	 */
	public function init() {
		if ( $this->is_add_button() ) {
			add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ), 15 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_localize_scripts' ), 13 );
			add_action( 'media_buttons', array( $this, 'shortcode_button' ) );
		}
	}


	/**
	 * Ajax handler for shortcode
	 *
	 * @since 2.3.0
	 */
	public function ajax_handler() {
		add_action( 'wp_ajax_give_shortcode', array( $this, 'shortcode_ajax' ) );
	}

	/**
	 * Register any TinyMCE plugins
	 *
	 * @param array $plugin_array
	 *
	 * @return array|bool
	 *
	 * @since 1.0
	 */
	public function mce_external_plugins( $plugin_array ) {

		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return false;
		}

		$plugin_array['give_shortcode'] = GIVE_PLUGIN_URL . 'includes/admin/shortcodes/mce-plugin.js';

		return $plugin_array;
	}

	/**
	 * Enqueue the admin assets
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function admin_enqueue_assets() {
		$direction = ( is_rtl() || isset( $_GET['d'] ) && 'rtl' === $_GET['d'] ) ? '.rtl' : '';

		wp_enqueue_script(
			'give_shortcode',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-shortcodes.js',
			array( 'jquery' ),
			GIVE_VERSION,
			true
		);

		wp_enqueue_style(
			'give-admin-shortcode-button-style',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-shortcode-button' . $direction . '.css',
			array(),
			GIVE_VERSION
		);
	}

	/**
	 * Localize the admin scripts
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function admin_localize_scripts() {

		if ( ! empty( self::$shortcodes ) ) {

			$variables = array();

			foreach ( self::$shortcodes as $shortcode => $values ) {
				if ( ! empty( $values['required'] ) ) {
					$variables[ $shortcode ] = $values['required'];
				}
			}

			wp_localize_script( 'give_shortcode', 'scShortcodes', $variables );
		}
	}

	/**
	 * Adds the "Donation Form" button above the TinyMCE Editor on add/edit screens.
	 *
	 * @return string|bool
	 *
	 * @since 1.0
	 */
	public function shortcode_button() {

		$shortcodes = array();

		foreach ( self::$shortcodes as $shortcode => $values ) {

			/**
			 * Filters the condition for including the current shortcode
			 *
			 * @since 1.0
			 */
			if ( apply_filters( sanitize_title( $shortcode ) . '_condition', true ) ) {

				$shortcodes[ $shortcode ] = sprintf(
					'<div class="sc-shortcode mce-menu-item give-shortcode-item-%1$s" data-shortcode="%2$s">%3$s</div>',
					$shortcode,
					$shortcode,
					$values['label']
				);
			}
		}

		if ( ! empty( $shortcodes ) ) {

			// check current WP version
			$img = ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) )
				? '<img src="' . GIVE_PLUGIN_URL . 'assets/dist/images/give-media.png" />'
				: '<span class="wp-media-buttons-icon" id="give-media-button" style="background-image: url(' . give_svg_icons( 'give_grey' ) . ');"></span>';

			reset( $shortcodes );

			if ( 1 === count( $shortcodes ) ) {

				$shortcode = key( $shortcodes );

				printf(
					'<button type="button" class="button sc-shortcode" data-shortcode="%s">%s</button>',
					$shortcode,
					sprintf(
						'%s %s %s',
						$img,
						__( 'Insert', 'give' ),
						self::$shortcodes[ $shortcode ]['label']
					)
				);
			} else {
				printf(
					'<div class="sc-wrap">' .
					'<button class="button sc-button" type="button">%s %s</button>' .
					'<div class="sc-menu mce-menu">%s</div>' .
					'</div>',
					$img,
					__( 'GiveWP Shortcodes', 'give' ),
					implode( '', array_values( $shortcodes ) )
				);
			}
		}
	}

	/**
	 * Load the shortcode dialog fields via AJAX
	 *
	 * @todo: handle error
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function shortcode_ajax() {
		if ( ! current_user_can( 'edit_give_forms' ) ) {
			wp_die();
		}

		$shortcode = isset( $_POST['shortcode'] ) ? $_POST['shortcode'] : false;
		$response  = false;

		if ( $shortcode && array_key_exists( $shortcode, self::$shortcodes ) ) {

			$data = self::$shortcodes[ $shortcode ];

			if ( ! empty( $data['errors'] ) ) {
				$data['btn_okay'] = array( esc_html__( 'Okay', 'give' ) );
			}

			$response = array(
				'body'      => $data['fields'],
				'close'     => $data['btn_close'],
				'ok'        => $data['btn_okay'],
				'shortcode' => $shortcode,
				'title'     => $data['title'],
			);
		} else {
			error_log( print_r( 'AJAX error!', 1 ) );
		}

		wp_send_json( $response );
	}


	/**
	 * Flag to check add shortcode button to current screen or not
	 *
	 * @since  2.1.0
	 * @access private
	 * @return bool
	 */
	private function is_add_button() {
		global $pagenow;

		$shortcode_button_pages = apply_filters(
			'give_shortcode_button_pages',
			array(
				'post.php',
				'page.php',
				'post-new.php',
				'post-edit.php',
				'edit.php',
			)
		);

		$exclude_post_types = array( 'give_forms' );

		/* @var WP_Screen $current_screen */
		$current_screen = get_current_screen();

		// Only run in admin post/page creation and edit screens
		if (
			! is_admin()
			|| ! in_array( $pagenow, $shortcode_button_pages )
			|| in_array( $current_screen->post_type, $exclude_post_types )

			/**
			 * Fire the filter
			 * Use this filter to show Give Shortcode button on custom pages
			 *
			 * @since 1.0
			 */
			|| ! apply_filters( 'give_shortcode_button_condition', true )
			|| empty( self::$shortcodes )
		) {
			return false;
		}

		return true;
	}
}

new Give_Shortcode_Button();
