<?php
/**
 * Shortcode Button Class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

defined( 'ABSPATH' ) or exit;

final class Give_Shortcode_Button {

	/**
	 * All shortcode tags
	 */
	public static $shortcodes;

	/**
	 * Construct
	 *
	 * @param
	 *
	 * @return
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_head',            array( $this, 'admin_head' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_localize_scripts' ), 13 );
			add_action( 'media_buttons',         array( $this, 'shortcode_button' ) );
		}

		add_action( "wp_ajax_give_shortcode",        array( $this, 'shortcode_ajax' ) );
		add_action( "wp_ajax_nopriv_give_shortcode", array( $this, 'shortcode_ajax' ) );
	}

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
	 * Register any TinyMCE plugins
	 *
	 * @param array $plugin_array
	 *
	 * @return array
	 */
	public function mce_external_plugins( $plugin_array ) {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		$plugin_array['give_shortcode'] = GIVE_PLUGIN_URL . 'assets/js/admin/tinymce/mce-plugin' . $suffix . '.js';

		return $plugin_array;
	}

	/**
	 * Enqueue the admin assets
	 *
	 * @return void
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
	 */
	public function admin_localize_scripts() {

		if ( ! empty( self::$shortcodes ) ) {

			$variables = array();

			foreach ( self::$shortcodes as $key => $value ) {
				$variables[ $key ] = $value['require'];
			}

			wp_localize_script( 'give_shortcode', 'scShortcodes', $variables );
		}
	}

	/**
	 * Adds the "Donation Form" button above the TinyMCE Editor on add/edit screens.
	 *
	 * @return string
	 */
	public function shortcode_button() {

		global $pagenow, $wp_version;

		// Only run in admin post/page creation and edit screens
		if ( in_array( $pagenow, ['post.php', 'page.php', 'post-new.php', 'post-edit.php'] )
			&& ! empty( self::$shortcodes ) ) {

			$shortcodes = array();

			foreach ( self::$shortcodes as $shortcode => $values ) {
				/**
				 * Filters the condition for including the current shortcode in the Shortcode Creator
				 * dropdown menu.
				 *
				 * @since 1.0.0
				 */
				if ( apply_filters( sanitize_title( $shortcode ) . '_condition', true ) ) {

					$shortcodes[ $shortcode ] = sprintf(
						'<div class="sc-shortcode mce-menu-item" data-shortcode="%s">%s</div>',
						$shortcode,
						$values['label']
					);
				}
			}

			if ( !empty( $shortcodes  ) ) {

				// check current WP version
				$img = ( version_compare( $wp_version, '3.5', '<' ) )
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
							__( 'Insert', 'give' ),
							self::$shortcodes[ $shortcode ]['label']
						)
					);
				} else {
					printf(
						'<div class="sc-wrap">' .
							'<button id="sc-button" class="button sc-button">%s %s</button>' .
							'<div id="sc-menu" class="sc-menu mce-menu">%s</div>' .
						'</div>',
						$img,
						__( 'Give Shortcodes', 'give' ),
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
	 */
	public function shortcode_ajax() {

		$shortcode = isset( $_POST['shortcode'] ) ? $_POST['shortcode'] : false;
		$response  = false;

		if ( $shortcode && array_key_exists( $shortcode, self::$shortcodes ) ) {

			$response = [
				'alert'     => self::$shortcodes[ $shortcode ]['alert'],
				'body'      => self::$shortcodes[ $shortcode ]['fields'],
				'close'     => self::$shortcodes[ $shortcode ]['btn_close'],
				'ok'        => self::$shortcodes[ $shortcode ]['btn_okay'],
				'shortcode' => $shortcode,
				'title'     => self::$shortcodes[ $shortcode ]['title'],
			];
		} else {
			// todo: handle error
			error_log( print_r( 'AJAX error!', 1 ) );
		}

		wp_send_json( $response );
	}
}

new Give_Shortcode_Button;
