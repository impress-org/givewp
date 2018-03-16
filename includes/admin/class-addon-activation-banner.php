<?php
/**
 * Give Activation Banner
 *
 * @author  WordImpress
 * @version 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $give_addons;

/**
 * Class Give_Addon_Activation_Banner
 *
 * @since  2.0.7 Added pleasing interface when multiple add-ons are activated.
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
		global $give_addons;

		// Append add-on information to the global variable.
		$give_addons[] = $_banner_details;

		// Get the currenct user.
		$current_user = wp_get_current_user();

		//Get current user
		$this->user_id = $current_user->ID;

		// Only if single add-on activated.
		if ( 1 === count( $give_addons ) ) {

			$this->plugin_activate_by   = 0;
			$this->banner_details       = $_banner_details;
			$this->test_mode            = ( $this->banner_details['testing'] == 'true' ) ? true : false;
			$this->nag_meta_key         = 'give_addon_activation_ignore_' . sanitize_title( $this->banner_details['name'] );
			$this->activate_by_meta_key = 'give_addon_' . sanitize_title( $this->banner_details['name'] ) . '_active_by_user';

			// Set up hooks.
			$this->init();

			// Store user id who activate plugin.
			$this->add_addon_activate_meta();
		}

		// Check if notice callback is already hacked.
		if ( ! $this->is_banner_notice_hooked() ) {
			// If multiple add-on are activated then show activation banner in tab view.
			add_action( 'admin_notices', array( $this, 'addon_activation_banner_notices' ), 10 );
		}
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
		$file_name = $this->get_plugin_file_name();

		if ( ! empty( $file_name ) ) {
			add_action( 'deactivate_' . $file_name, array( $this, 'remove_addon_activate_meta' ) );
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

				if ( empty( $file_name ) ) {
					return false;
				}

				foreach ( $active_plugins as $plugin ) {
					if ( false !== strpos( $plugin, $file_name ) ) {
						$file_name = $plugin;
						break;
					}
				}
			} elseif ( WP_DEBUG ) {
				throw new Exception( __( "File path must be added within the {$this->banner_details['name']} add-on in the banner details.", 'give' ) );
			}

			// Check plugin path calculated by addon file path.
			if ( empty( $file_name ) && WP_DEBUG ) {
				throw new Exception( __( "Empty add-on plugin path for {$this->banner_details['name']} add-on.", 'give' ) );
			}

		} catch ( Exception $e ) {
			echo $e->getMessage();
		}

		return $file_name;
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
	 * Check if the addon_activation_banner_notices function has already been hooked to admin_notice.
	 *
	 * @since 2.0.7
	 *
	 * @return bool
	 */
	public function is_banner_notice_hooked() {
		global $wp_filter;
		$notice_already_hooked = false;

		if ( isset( $wp_filter['admin_notices']->callbacks[10] ) ) {

			// Get all of the hooks.
			$admin_notice_callbacks = array_keys( $wp_filter['admin_notices']->callbacks[10] );

			foreach ( $admin_notice_callbacks as $key ) {
				//If the key is found in your string, set $found to true
				if ( false !== strpos( $key, "addon_activation_banner_notices" ) ) {
					$notice_already_hooked = true;
				}
			}
		}

		return $notice_already_hooked;
	}

	/**
	 * Get the add-on banner notices.
	 *
	 * @since 2.0.7
	 */
	public function addon_activation_banner_notices() {
		global $pagenow, $give_addons;

		// Bailout.
		if ( 'plugins.php' !== $pagenow || $this->user_id !== $this->plugin_activate_by ) {
			return;
		}

		// If only one add-on activated.
		$is_single = 1 === count( $give_addons ) ? true : false;

		// If the user hasn't already dismissed the alert, output activation banner.
		if ( ! get_user_meta( $this->user_id, $this->get_notice_dismiss_meta_key() ) ) {
			ob_start();

			// Output inline styles here because there's no reason
			// to enqueued them after the alert is dismissed.
			$this->print_css_js();
			ob_start();
			?>
			<div class="<?php echo ( false === $is_single ) ? 'give-alert-tab-wrapper' : ''; ?> updated give-addon-alert give-notice">
				<?php
				// If multiple add-on are activated.
				if ( false === $is_single ) {
					?>
					<div class="give-vertical-tab">
						<ul class="give-alert-addon-list">
							<?php
							$is_first = true;
							foreach ( $give_addons as $banner ) {
								?>
								<li class="give-tab-list <?php echo ( true === $is_first ) ? ' active' : ''; ?>"
								    id="give-addon-<?php echo esc_html( basename( $banner['file'], '.php' ) ); ?>">
									<a href="#"><?php echo esc_html( $banner['name'] ); ?></a>
								</li>
								<?php
								$is_first = false;
							}
							$is_first = true;
							?>
						</ul>
						<div class="give-right-side-block">
							<?php
							foreach ( $give_addons as $banner ) {
								?>
								<div
										class="give-tab-details <?php echo ( true === $is_first ) ? ' active' : ''; ?> "
										id="give-addon-<?php echo esc_html( basename( $banner['file'], '.php' ) ); ?>"
								>
									<?php
									// Get the notice meta key.
									$meta_key = ( 1 === count( $give_addons ) )
										? $this->nag_meta_key
										: 'give_addon_activation_ignore_all';

									$this->render_single_addon_banner( $banner, $meta_key );
									?>
								</div>
								<?php
								$is_first = false;
							}
							?>
						</div>
					</div>
					<?php
				} else {
					$this->render_single_addon_banner( $give_addons[0] );
				}
				?>
			</div>
			<?php
			$notice_html = ob_get_clean();

			// Register notice.
			Give()->notices->register_notice( array(
				'id'               => 'give_add_on_activation_notice',
				'type'             => 'updated',
				'description_html' => $notice_html,
				'show'             => true,
			) );
		}
	}


	/**
	 * Add activation banner css.
	 *
	 * @since 1.8.16
	 * @access private
	 */
	private function print_css(){
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
		<?php
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
}