<?php
/**
 * Shortcode Button Class
 *
 * @package     Give
 * @subpackage  Admin
 * @author      Paul Ryley
 * @copyright   Copyright (c) 2016, WordImpress
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

		if ( is_admin() ) {
			add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ), 15 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_localize_scripts' ), 13 );
			add_action( 'media_buttons', array( $this, 'shortcode_button' ) );
		}

		add_action( "wp_ajax_give_shortcode", array( $this, 'shortcode_ajax' ) );
		add_action( "wp_ajax_nopriv_give_shortcode", array( $this, 'shortcode_ajax' ) );
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

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		$plugin_array['give_shortcode'] = GIVE_PLUGIN_URL . 'assets/js/admin/tinymce/mce-plugin' . $suffix . '.js';

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

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script(
			'give_shortcode',
			GIVE_PLUGIN_URL . 'assets/js/admin/admin-shortcodes' . $suffix . '.js',
			array( 'jquery' ),
			GIVE_VERSION,
			true
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

		$screen = get_current_screen();

		// If we load wp editor by ajax then $screen will be empty which generate notice if we treat $screen as WP_Screen object.
		// For example we are loading wp editor by ajax in repeater field.
		if ( ! ( $screen instanceof WP_Screen ) ) {
			return false;
		}

		$shortcode_button_pages = apply_filters( 'give_shortcode_button_pages', array(
			'post.php',
			'page.php',
			'post-new.php',
			'post-edit.php',
			'edit.php',
			'edit.php?post_type=page',
		) );

		// Only run in admin post/page creation and edit screens
		if ( in_array( $screen->parent_file, $shortcode_button_pages )
		     && apply_filters( 'give_shortcode_button_condition', true )
		     && ! empty( self::$shortcodes )
		) {

			$shortcodes = array();

			foreach ( self::$shortcodes as $shortcode => $values ) {

				/**
				 * Filters the condition for including the current shortcode
				 *
				 * @since 1.0
				 */
				if ( apply_filters( sanitize_title( $shortcode ) . '_condition', true ) ) {

					$shortcodes[ $shortcode ] = sprintf(
						'<div class="sc-shortcode mce-menu-item give-shortcode-item-%1$s" data-shortcode="%s">%s</div>',
						$shortcode,
						$values['label'],
						$shortcode
					);
				}
			}

			if ( ! empty( $shortcodes ) ) {

				// check current WP version
				$img = ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) )
					? '<img src="' . GIVE_PLUGIN_URL . 'assets/images/give-media.png" />'
					: '<span class="wp-media-buttons-icon" id="give-media-button" style="background-image: url(' . give_svg_icons( 'give_grey' ) . ');"></span>';

				reset( $shortcodes );

				if ( count( $shortcodes ) == 1 ) {

					$shortcode = key( $shortcodes );

					printf(
						'<button class="button sc-shortcode" data-shortcode="%s">%s</button>',
						$shortcode,
						sprintf( '%s %s %s',
							$img,
							esc_html__( 'Insert', 'give' ),
							self::$shortcodes[ $shortcode ]['label']
						)
					);
				} else {
					printf(
						'<div class="sc-wrap">' .
						'<button class="button sc-button">%s %s</button>' .
						'<div class="sc-menu mce-menu">%s</div>' .
						'</div>',
						$img,
						esc_html__( 'Give Shortcodes', 'give' ),
						implode( '', array_values( $shortcodes ) )
					);
				}
			}
		}
	}

	/**
	 * Load the shortcode dialog fields via AJAX
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function shortcode_ajax() {

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
			// todo: handle error
			error_log( print_r( 'AJAX error!', 1 ) );
		}

		wp_send_json( $response );
	}
}

new Give_Shortcode_Button;
