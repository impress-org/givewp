<?php
/**
 * Give Activation Banner
 *
 * @author  WordImpress
 * @version 1.0
 * https://github.com/WordImpress/plugin-activation-banner-demo
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Addon_Activation_Banner
 */
class Give_Addon_Activation_Banner {

	/**
	 * Class constructor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $_banner_details {
	 *                               'file'              => __FILE__, // (required) Directory path to the main plugin file
	 *                               'name'              => __( 'Authorize.net Gateway', 'give-authorize' ), // (required) Name of the Add-on
	 *                               'version'           => GIVE_AUTHORIZE_VERSION, // (required)The most current version
	 *                               'documentation_url' => 'http://docs.givewp.com/addon-authorize',// (required)
	 *                               'support_url'       => 'https://givewp.com/support/', // (required)Location of Add-on settings page, leave blank to hide
	 *                               'testing'           => false, // (required) Never leave as "true" in production!!!
	 *                               }
	 */
	function __construct( $_banner_details ) {
		$current_user = wp_get_current_user();

		$this->plugin_activate_by   = 0;
		$this->banner_details       = $_banner_details;
		$this->test_mode            = ( $this->banner_details['testing'] == 'true' ) ? true : false;
		$this->nag_meta_key         = 'give_addon_activation_ignore_' . sanitize_title( $this->banner_details['name'] );
		$this->activate_by_meta_key = 'give_addon_' . sanitize_title( $this->banner_details['name'] ) . '_active_by_user';

		//Get current user
		$this->user_id = $current_user->ID;

		// Set up hooks.
		$this->init();

		// Store user id who activate plugin.
		$this->add_addon_activate_meta();
	}

	/**
	 * Set up WordPress filters to hook into WP's update process.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function init() {

		//Testing?
		if ( $this->test_mode ) {
			delete_user_meta( $this->user_id, $this->nag_meta_key );
		}

		//Get the current page to add the notice to
		add_action( 'current_screen', array( $this, 'give_addon_notice_ignore' ) );
		add_action( 'admin_notices', array( $this, 'give_addon_activation_admin_notice' ) );

		// File path of addon must be included in banner detail other addon activate meta will not delete.
		add_action( 'deactivate_' . $this->get_plugin_file_name(), array( $this, 'remove_addon_activate_meta' ) );
	}


	/**
	 * Check if current page is plugin page or not.
	 *
	 * @since  1.8
	 * @access private
	 * @return bool
	 */
	private function is_plugin_page() {
		$screen = get_current_screen();

		return ( $screen->parent_file === 'plugins.php' );
	}


	/**
	 * Give Addon Activation Banner
	 *
	 * @since  1.0
	 * @access public
	 */
	public function give_addon_activation_admin_notice() {

		// Bailout.
		if ( ! $this->is_plugin_page() || $this->user_id !== $this->plugin_activate_by ) {
			return;
		}

		// If the user hasn't already dismissed the alert, output activation banner.
		if ( ! get_user_meta( $this->user_id, $this->nag_meta_key ) ) {

			// Output inline styles here because there's no reason
			// to enqueued them after the alert is dismissed.
			?>
			<style>
				div.give-addon-alert.updated {
					padding: 20px;
					position: relative;
					border-color: #66BB6A;
				}

				div.give-alert-message {
					margin-left: 70px;
				}

				div.give-addon-alert img.give-logo {
					max-width: 50px;
					float: left;
				}

				div.give-addon-alert h3 {
					margin: -5px 0 10px;
					font-size: 22px;
					font-weight: 300;
					line-height: 30px;
				}

				div.give-addon-alert h3 span {
					font-weight: 700;
					color: #66BB6A;
				}

				div.give-addon-alert .alert-actions {
				}

				div.give-addon-alert a {
					color: #66BB6A;
				}

				div.give-addon-alert .alert-actions a {
					margin-right: 2em;
				}

				div.give-addon-alert .alert-actions a {
					text-decoration: underline;
				}

				div.give-addon-alert .alert-actions a:hover {
					color: #555555;
				}

				div.give-addon-alert .alert-actions a span {
					text-decoration: none;
					margin-right: 5px;
				}

				div.give-addon-alert .dismiss {
					position: absolute;
					right: 20px;
					height: 100%;
					top: 50%;
					margin-top: -10px;
					outline: none;
					box-shadow: none;
					text-decoration: none;
					color: #AAA;
				}

				div.give-addon-alert .dismiss:hover {
					color: #333;
				}
			</style>

			<div class="updated give-addon-alert">

				<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/images/svg/give-icon-full-circle.svg" class="give-logo"/>

				<div class="give-alert-message">
					<h3><?php
						printf(
						/* translators: %s: Add-on name */
							esc_html__( "Thank you for installing Give's %s Add-on!", 'give' ),
							'<span>' . $this->banner_details['name'] . '</span>'
						);
						?></h3>

					<a href="<?php
					//The Dismiss Button.
					$nag_admin_dismiss_url = 'plugins.php?' . $this->nag_meta_key . '=0';
					echo admin_url( $nag_admin_dismiss_url ); ?>" class="dismiss"><span
							class="dashicons dashicons-dismiss"></span></a>

					<div class="alert-actions">

						<?php //Point them to your settings page.
						if ( isset( $this->banner_details['settings_url'] ) ) { ?>
							<a href="<?php echo $this->banner_details['settings_url']; ?>">
								<span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'Go to Settings', 'give' ); ?>
							</a>
						<?php } ?>

						<?php
						// Show them how to configure the Addon.
						if ( isset( $this->banner_details['documentation_url'] ) ) { ?>
							<a href="<?php echo $this->banner_details['documentation_url'] ?>" target="_blank">
								<span class="dashicons dashicons-media-text"></span><?php
								printf(
								/* translators: %s: Add-on name */
									esc_html__( 'Documentation: %s Add-on', 'give' ),
									$this->banner_details['name']
								);
								?></a>
						<?php } ?>
						<?php
						//Let them signup for plugin updates
						if ( isset( $this->banner_details['support_url'] ) ) { ?>

							<a href="<?php echo $this->banner_details['support_url'] ?>" target="_blank">
								<span class="dashicons dashicons-sos"></span><?php esc_html_e( 'Get Support', 'give' ); ?>
							</a>

						<?php } ?>

					</div>
				</div>
			</div>
			<?php
		}
	}


	/**
	 * Ignore Nag.
	 *
	 * This is the action that allows the user to dismiss the banner it basically sets a tag to their user meta data
	 *
	 * @since  1.0
	 * @access public
	 */
	public function give_addon_notice_ignore() {

		/**
		 * If user clicks to ignore the notice, add that to their user meta the banner then checks whether this tag exists already or not.
		 * See here: http://codex.wordpress.org/Function_Reference/add_user_meta
		 */
		if ( isset( $_GET[ $this->nag_meta_key ] ) && '0' == $_GET[ $this->nag_meta_key ] ) {

			//Get the global user
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;

			add_user_meta( $user_id, $this->nag_meta_key, 'true', true );
		}
	}

	/**
	 * Setup user id to option
	 *
	 * @since  1.8
	 * @access private
	 */
	private function add_addon_activate_meta() {
		$user_id                  = get_option( $this->activate_by_meta_key );
		$this->plugin_activate_by = (int) $user_id;

		if ( ! $user_id ) {
			add_option( $this->activate_by_meta_key, $this->user_id, '', 'no' );
			$this->plugin_activate_by = (int) $this->user_id;
		}
	}


	/**
	 * Delete user id from option if plugin deactivated.
	 *
	 * @since  1.8
	 * @access public
	 */
	public function remove_addon_activate_meta() {
		$user_id = get_option( $this->activate_by_meta_key );

		if ( $user_id ) {
			delete_option( $this->activate_by_meta_key );
		}
	}


	/**
	 * Get plugin file name.
	 *
	 * @since   1.8
	 * @access  private
	 * @return mixed
	 */
	private function get_plugin_file_name() {
		$active_plugins = get_option( 'active_plugins' );
		$file_name      = '';

		try {

			// Check addon file path.
			if ( ! empty( $this->banner_details['file'] ) ) {
				$file_name = '';
				if ( $file_path = explode( '/plugins/', $this->banner_details['file'] ) ) {
					$file_path = array_pop( $file_path );
					$file_name = current( explode( '/', $file_path ) );
				}

				foreach ( $active_plugins as $plugin ) {
					if ( false !== strpos( $plugin, $file_name ) ) {
						$file_name = $plugin;
						break;
					}
				}
			} else {
				throw new Exception( __( "File path must be added of {$this->banner_details['name']} addon in banner details.", 'give' ) );
			}

			// Check plugin path calculated by addon file path.
			if ( empty( $file_name ) ) {
				throw new Exception( __( "Empty Addon plugin path for {$this->banner_details['name']} addon.", 'give' ) );
			}

		} catch ( Exception $e ) {
			echo $e->getMessage();
		}

		return $file_name;
	}

}
