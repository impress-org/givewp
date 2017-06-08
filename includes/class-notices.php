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
	 * @since  1.8
	 * @access private
	 */
	private static $notices = array();


	/**
	 * Flag to check if any notice auto dismissible among all notices
	 *
	 * @since  1.8.9
	 * @access private
	 * @var bool
	 */
	private static $has_auto_dismissible_notice = false;

	/**
	 * Flag to check if any notice has dismiss interval among all notices
	 *
	 * @since  1.8.9
	 * @access private
	 * @var bool
	 */
	private static $has_dismiss_interval_notice = false;

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'render_notices' ), 999 );
		add_action( 'give_dismiss_notices', array( $this, 'dismiss_notices' ) );
		add_action( 'give_hide_notice', array( $this, 'hide_notice' ) );
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
				'id'                    => '',
				'description'           => '',
				'auto_dismissible'      => false,

				// Value: error/updated
				'type'                  => 'error',

				// Value: null/user/all
				'dismissible_type'      => null,

				// Value: shortly/permanent/null/custom/(interval time in second)
				'dismiss_interval'      => null,

				// Only set it when custom is defined.
				'dismiss_interval_time' => null,

			)
		);

		// Bailout.
		if ( empty( $notice_args['id'] ) ) {
			return false;
		}

		self::$notices[ $notice_args['id'] ] = $notice_args;

		// Auto set show param if not already set.
		if ( ! isset( self::$notices[ $notice_args['id'] ]['show'] ) ) {
			self::$notices[ $notice_args['id'] ]['show'] = $this->is_notice_dismissed( $notice_args ) ? false : true;
		}

		// Auto set time interval for shortly.
		if ( 'shortly' === self::$notices[ $notice_args['id'] ]['dismiss_interval'] ) {
			self::$notices[ $notice_args['id'] ]['dismiss_interval_time'] = DAY_IN_SECONDS;
		}

		return true;
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
	 * Display notice.
	 *
	 * @since 1.8.9
	 *
	 */
	public function render_notices() {
		// Bailout.
		if ( empty( self::$notices ) ) {
			return;
		}

		$output = '';

		foreach ( self::$notices as $notice_id => $notice ) {
			// Check flag set to true to show notice.
			if ( ! $notice['show'] ) {
				continue;
			}

			// Check if notice dismissible or not.
			if ( ! self::$has_auto_dismissible_notice ) {
				self::$has_auto_dismissible_notice = $notice['auto_dismissible'];
			}

			// Check if notice dismissible or not.
			if ( ! self::$has_dismiss_interval_notice ) {
				self::$has_dismiss_interval_notice = $notice['dismiss_interval'];
			}

			$css_id = ( false === strpos( $notice['id'], 'give' ) ? "give-{$notice['id']}" : $notice['id'] );

			$css_class = $notice['type'] . ' give-notice notice is-dismissible';
			$output    .= sprintf(
				'<div id="%1$s" class="%2$s" data-auto-dismissible="%3$s" data-dismissible-type="%4$s" data-dismiss-interval="%5$s" data-notice-id="%6$s" data-security="%7$s" data-dismiss-interval-time="%8$s">' . " \n",
				$css_id,
				$css_class,
				$notice['auto_dismissible'],
				$notice['dismissible_type'],
				$notice['dismiss_interval'],
				$notice['id'],
				wp_create_nonce( "give_edit_{$notice_id}_notice" ),
				$notice['dismiss_interval_time']
			);
			$output    .= "<p>{$notice['description']}</p>";
			$output    .= "</div> \n";
		}

		echo $output;

		$this->print_js();
	}

	/**
	 * Print notice js.
	 *
	 * @since  1.8.9
	 * @access private
	 */
	private function print_js() {
		if ( self::$has_auto_dismissible_notice ) :
			?>
			<script>
				jQuery(document).ready(function () {
					// auto hide setting message in 5 seconds.
					window.setTimeout(
						function () {
							jQuery('.give-notice[data-auto-dismissible="1"]').slideUp();
						},
						5000
					);
				})
			</script>
			<?php
		endif;

		if ( self::$has_dismiss_interval_notice ) :
			?>
			<script>
				jQuery(document).ready(function () {

					jQuery('body').on('click', 'button.notice-dismiss', function (e) {
						var $parent = jQuery(this).parents('.give-notice');

						// bailout.
						if (!$parent.data('dismiss-interval') || !$parent.data('dismissible-type')) {
							return false;
						}

						e.preventDefault();

						var data = {
							'give-action'     : 'hide_notice',
							'notice_id'       : $parent.data('notice-id'),
							'dismissible_type': $parent.data('dismissible-type'),
							'dismiss_interval': $parent.data('dismiss-interval'),
							'_wpnonce'        : $parent.data('security')
						};

						jQuery.post(
							'<?php echo admin_url(); ?>admin-ajax.php',
							data,
							function (response) {

							})
					})
				});
			</script>
			<?php
		endif;
	}


	/**
	 * Hide notice.
	 *
	 * @since  1.8.9
	 * @access public
	 */
	public function hide_notice() {
		$_post     = give_clean( $_POST );
		$notice_id = esc_attr( $_post['notice_id'] );

		// Bailout.
		if ( empty( $notice_id ) || ! check_ajax_referer( "give_edit_{$notice_id}_notice", '_wpnonce' ) ) {
			wp_send_json_error();
		}

		$notice_key = Give()->notices->get_notice_key( $notice_id, $_post['dismiss_interval'] );
		if ( 'user' === $_post['dismissible_type'] ) {
			$current_user = wp_get_current_user();
			$notice_key   = Give()->notices->get_notice_key( $notice_id, $_post['dismiss_interval'], $current_user->ID );
		}

		$notice_dismiss_time = ! empty( $_post['dismiss_interval_time'] ) ? $_post['dismiss_interval_time'] : null;

		// Save option to hide notice.
		Give_Cache::set( $notice_key, true, $notice_dismiss_time, true );

		wp_send_json_success();
	}


	/**
	 * Get notice key.
	 *
	 * @since  1.8.9
	 * @access public
	 *
	 * @param string $notice_id
	 * @param string $dismiss_interval
	 * @param int    $user_id
	 *
	 * @return string
	 */
	public function get_notice_key( $notice_id, $dismiss_interval, $user_id = 0 ) {
		$notice_key = sanitize_key( "_give_notice_{$notice_id}_{$dismiss_interval}" );

		if ( $user_id ) {
			$notice_key = sanitize_key( "_give_notice_{$notice_id}_{$dismiss_interval}_{$user_id}" );
		}

		return $notice_key;
	}


	/**
	 * Check if notice dismissed or not
	 *
	 * @since  1.8.9
	 * @access public
	 *
	 * @param array $notice
	 *
	 * @return bool|null
	 */
	public function is_notice_dismissed( $notice ) {
		$notice_key = $this->get_notice_key( $notice['id'], $notice['dismiss_interval'] );

		if ( 'user' === $notice['dismissible_type'] ) {
			$current_user = wp_get_current_user();
			$notice_key   = Give()->notices->get_notice_key( $notice['id'], $notice['dismiss_interval'], $current_user->ID );
		}

		$notice_data = Give_Cache::get( $notice_key, true );

		return ! empty( $notice_data ) && ! is_wp_error( $notice_data );
	}
}