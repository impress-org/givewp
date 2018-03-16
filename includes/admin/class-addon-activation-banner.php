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

		// Remove the flag of the dimissed notice when any Give's add-on activated.
		add_action( 'activate_plugin', array( $this, 'give_addon_activation' ), 10, 1 );
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

		// File path of addon must be included in banner detail other addon activate meta will not delete.
		$file_name = $this->get_plugin_file_name();

		if ( ! empty( $file_name ) ) {
			add_action( 'deactivate_' . $file_name, array( $this, 'remove_addon_activate_meta' ) );
		}
	}

	/**
	 * Delete meta from the user meta table when any Give's Add-on activated.
	 *
	 * @since 2.0.7
	 *
	 * @param array $plugin_file Plugin filename.
	 */
	public function give_addon_activation( $plugin_file ) {
		if ( strpos( $plugin_file, 'Give-' ) !== false ) {
			delete_user_meta( $this->user_id, 'give_addon_activation_ignore_all' );
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
	 * Get the notice dismiss meta key.
	 *
	 * @since 2.0.7
	 */
	public function get_notice_dismiss_meta_key() {
		global $give_addons;

		// Get the notice meta key.
		$notice_meta_key = ( 1 === count( $give_addons ) )
			? $this->nag_meta_key
			: 'give_addon_activation_ignore_all';

		// Return meta key.
		return $notice_meta_key;
	}

	/**
	 * Add activation banner css and js .
	 *
	 * @since  1.8.16
	 * @since  2.0.7 Added JS code for multiple add-on.
	 * @access private
	 */
	private function print_css_js() {
		?>
		<style>
			div.give-addon-alert.updated {
				padding: 20px;
				position: relative;
				border-color: #66BB6A;
				min-height: 85px;
			}

			div.give-alert-message {
				margin-left: 108px;
			}

			div.give-addon-alert img.give-logo {
				max-width: 85px;
				float: left;
			}

			div.give-addon-alert h3 {
				margin: -5px 0 10px;
				font-size: 22px;
				font-weight: 400;
				line-height: 30px;
			}

			div.give-addon-alert h3 span {
				font-weight: 700;
				color: #66BB6A;
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
				right: 0px;
				height: 99%;
				top: 23%;
				margin-top: -10px;
				outline: none;
				box-shadow: none;
				text-decoration: none;
				color: #AAA;
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

			.give-alert-tab-wrapper .dismiss {
				right: 0px !important;
				height: 99% !important;
				top: 23% !important;
			}

			div.give-addon-alert .dismiss:hover {
				color: #333;
			}

			ul.give-alert-addon-list {
				min-width: 220px;
				display: inline-block;
				float: left;
				max-width: 250px;
				padding: 0;
				margin: 0;
			}

			.give-addon-alert .give-addon-description {
				padding: 1px;
				display: inline-block;
				color: #777;
				margin-bottom: 12px;
			}

			.give-alert-tab-wrapper .give-right-side-block {
				width: calc(100% - 250px);
				display: inline-block;
				float: left;
				background: #fff;
				height: 100%;
				position: relative;
			}

			.give-vertical-tab {
				width: 100%;
			}

			ul.give-alert-addon-list li {
				display: block;
				border: 1px solid #d1d1d18f;
				border-width: 1px 0px 0px 0px;
				margin: 0;
			}

			ul.give-alert-addon-list li a {
				display: block;
				font-weight: bold;
				color: #a3a3a3;
				text-transform: capitalize;
				text-decoration: none;
				padding: 15px 10px 15px;
				box-shadow: inset -6px 0px 18px 0px rgba(204, 204, 204, 0.36);
				-moz-box-shadow: inset -6px 0px 18px 0px rgba(204, 204, 204, 0.36);
				-webkit-box-shadow: inset -6px 0px 18px 0px rgba(204, 204, 204, 0.36);
				-ms-box-shadow: inset -6px 0px 18px 0px rgba(204, 204, 204, 0.36);
				-o-box-shadow: inset -6px 0px 18px 0px rgba(204, 204, 204, 0.36);
			}

			ul.give-alert-addon-list li.give-tab-list.active a {
				color: #5f6c74;
				box-shadow: none;
			}

			.updated.give-addon-alert.give-notice.give-alert-tab-wrapper {
				display: inline-block;
				width: 100%;
			}

			.give-tab-details {
				display: none;
				min-height: 100px;
				position: absolute;
				top: 0;
				left: 0;
				padding: 20px 20px 20px 40px;
			}

			.give-tab-details.active {
				display: block;
				z-index: 1;
				position: relative;
			}

			.give-alert-tab-wrapper.give-addon-alert img.give-logo {
				max-width: 80px;
			}

			.give-alert-tab-wrapper .give-alert-message {
				margin-left: 100px;
				padding-top: 10px;
			}

			ul.give-alert-addon-list li.give-tab-list.active {
				background: #fff;
			}

			ul.give-alert-addon-list li.give-tab-list:last-child {
				border-bottom: 0px solid #ccc;
			}

			ul.give-alert-addon-list li.give-tab-list:first-child {
				border-top: 0 none;
			}

			.give-alert-tab-wrapper {
				padding: 0 !important;
			}

			/** Responsiveness */
			@media screen and (max-width: 767px) {
				.give-alert-tab-wrapper .give-tab-details {
					padding: 20px 40px 20px 20px;
				}

				.give-alert-tab-wrapper .give-right-side-block {
					width: 100%;
				}

				.give-alert-tab-wrapper ul.give-alert-addon-list {
					min-width: 100%;
				}
			}
		</style>
		<?php
	}

	/**
	 * Render single banner activation
	 *
	 * @since 1.0.0
	 *
	 * @param array  $banner_arr Banner options.
	 * @param string $meta_key   Pass meta key.
	 */
	private function render_single_addon_banner( $banner_arr, $meta_key = '' ) {
		// Get the add-on details.
		$plugin_data = get_plugin_data( $banner_arr['file'] );
		?>
		<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/images/svg/give-icon-full-circle.svg" class="give-logo" />
		<div class="give-alert-message">
			<h3>
				<?php
				printf(
				/* translators: %s: Add-on name */
					esc_html__( "New Give Add-on Activated: %s", 'give' ),
					'<span>' . $banner_arr['name'] . '</span>'
				);
				?>
			</h3>
			<?php
			$meta_key              = empty( $meta_key ) ? $this->nag_meta_key : $meta_key;
			$nag_admin_dismiss_url = admin_url( 'plugins.php?' . $meta_key . '=0' );
			?>
			<a href="<?php echo $nag_admin_dismiss_url; ?>" class="dismiss">
				<span class="dashicons dashicons-dismiss"></span>
			</a>
			<div class="alert-actions">
				<?php //Point them to your settings page.
				if ( ! empty( $plugin_data['Description'] ) ) {
					?><span class="give-addon-description"><em><?php echo strip_tags( $plugin_data['Description'] ); ?></em></span><br />
					<?php
				}
				if ( isset( $banner_arr['settings_url'] ) ) { ?>
					<a href="<?php echo $banner_arr['settings_url']; ?>"><span class="dashicons dashicons-admin-settings"></span>
						<?php esc_html_e( 'Go to Settings', 'give' ); ?>
					</a>
					<?php
				}
				// Show them how to configure the Addon.
				if ( isset( $banner_arr['documentation_url'] ) ) { ?>
					<a href="<?php echo $banner_arr['documentation_url'] ?>" target="_blank">
						<span class="dashicons dashicons-media-text"></span><?php
						printf(
						/* translators: %s: Add-on name */
							esc_html__( 'Documentation: %s Add-on', 'give' ),
							$banner_arr['name']
						);
						?></a>
				<?php } ?>
				<?php
				//Let them signup for plugin updates
				if ( isset( $banner_arr['support_url'] ) ) { ?>
					<a href="<?php echo $banner_arr['support_url'] ?>" target="_blank">
						<span class="dashicons dashicons-sos"></span><?php esc_html_e( 'Get Support', 'give' ); ?>
					</a>
				<?php } ?>
			</div>
		</div>
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
		global $give_addons;

		// Get the notice meta key.
		$notice_meta_key = ( 1 === count( $give_addons ) )
			? $this->nag_meta_key
			: 'give_addon_activation_ignore_all';

		/**
		 * If user clicks to ignore the notice, add that to their user meta the banner then checks whether this tag exists already or not.
		 * See here: http://codex.wordpress.org/Function_Reference/add_user_meta
		 */
		if ( isset( $_GET[ $notice_meta_key ] ) && '0' === $_GET[ $notice_meta_key ] ) {

			//Get the global user
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;

			add_user_meta( $user_id, $notice_meta_key, 'true', true );
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