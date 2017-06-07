<?php
/**
 * Admin Notices Class.
 *
 * @package     Give
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Notices Class
 *
 * @since 1.0
 */
class Give_Notices {
	/**
	 * List of notices
	 * @var array
	 * @since 1.8
	 */
	private static $notices = array();

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ), 999 );
		add_action( 'give_dismiss_notices', array( $this, 'dismiss_notices' ) );
		add_action( 'admin_bar_menu', array( $this, 'give_admin_bar_menu' ), 1000, 1 );
		add_action( 'admin_footer', '_give_admin_quick_js' );
	}


	/**
	 * Register notice.
	 *
	 * @since  1.8.9
	 * @access public
	 *
	 * @param $notice_args
	 *
	 * @return bool
	 */
	public function register_notice( $notice_args ) {
		$notice_args = wp_parse_args(
			$notice_args,
			array(
				'type'             => 'error',
				'id'               => '',
				'description'      => '',
				// Value: null/user/all
				'dismissible_type' => null,
				'auto_dismissible' => false,
				'show'             => false,
			)
		);

		if ( empty( $notice_args['id'] ) ) {
			return false;
		}

		self::$notices[ $notice_args['id'] ] = $notice_args;

		return true;
	}

	/**
	 * Register notice.
	 *
	 * @since  1.8.9
	 * @access public
	 *
	 * @param string $notice_id
	 *
	 * @return bool
	 */
	public function is_show_notice( $notice_id ) {
		return array_key_exists( $notice_id, self::$notices ) && self::$notices[$notice_id]['show'];
	}


	/**
	 * Display admin bar when active.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return bool
	 */
	public function give_admin_bar_menu( $wp_admin_bar ) {

		if ( ! give_is_test_mode() || ! current_user_can( 'view_give_reports' ) ) {
			return false;
		}

		// Add the main siteadmin menu item.
		$wp_admin_bar->add_menu( array(
			'id'     => 'give-test-notice',
			'href'   => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' ),
			'parent' => 'top-secondary',
			'title'  => esc_html__( 'Give Test Mode Active', 'give' ),
			'meta'   => array( 'class' => 'give-test-mode-active' ),
		) );

	}

	/**
	 * Show relevant notices.
	 *
	 * @since 1.0
	 */
	public function show_notices() {
		$this->settings_errors();
	}


	/**
	 * Admin Add-ons Notices.
	 *
	 * @since 1.0
	 * @return void
	 */
	function give_admin_addons_notices() {
		add_settings_error( 'give-notices', 'give-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'give' ), 'error' );
		settings_errors( 'give-notices' );
	}


	/**
	 * Dismiss admin notices when Dismiss links are clicked.
	 *
	 * @since 1.0
	 * @return void
	 */
	function dismiss_notices() {
		if ( isset( $_GET['give_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_give_' . $_GET['give_notice'] . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'give_action', 'give_notice' ) ) );
			exit;
		}
	}


	/**
	 * Get give style admin notice.
	 *
	 * @since  1.8
	 * @access public
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @return string
	 */
	public static function notice_html( $message, $type = 'updated' ) {
		ob_start();
		?>
		<div class="<?php echo $type; ?> notice">
			<p><?php echo $message; ?></p>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Display settings errors registered by add_settings_error().
	 *
	 * @since 1.8.9
	 *
	 */
	private function settings_errors() {
		// Bailout.
		if( empty( self::$notices ) ) {
			return;
		}

		$output = '';

		foreach ( self::$notices as $notice_id => $notice ) {
			$css_id = (  false === strpos( $notice['id'], 'give') ? "give-{$notice['id']}" : $notice['id'] );

			$css_class = $notice['type'] . ' give-notice notice is-dismissible';
			$output .= "<div id=\"{$css_id}\" class=\"{$css_class}\" data-auto-dismissible=\"{$notice['auto_dismissible']}\"> \n";
			$output .= "<p>{$notice['description']}</p>";
			$output .= "</div> \n";
		}

		echo $output;
	}

	/**
	 * Print js for admin pages.
	 *
	 * @since 1.8.7
	 */
	function _give_admin_quick_js() {
		/* @var WP_Screen $screen */
		$screen = get_current_screen();

		if( ! ( $screen instanceof WP_Screen ) ) {
			return false;
		}

		switch ( true ) {
			case Give()->notices->is_show_notice( 'give-invalid-php-version' ):
				?>
				<script>
					jQuery(document).ready(function ($) {
						$('.give-outdated-php-notice').on('click', 'button.notice-dismiss', function (e) {

							e.preventDefault();

							var data = {
								'action': 'give_hide_outdated_php_notice',
								'_give_hide_outdated_php_notices_shortly': 'general'
							};

							jQuery.post('<?php echo admin_url(); ?>admin-ajax.php', data, function(response) { });

						});
					});
				</script>
				<?php
		}
	}

}