<?php
/**
 * Give Activation Banner
 *
 * @author  GiveWP
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
 * @since  2.1.0 Added pleasing interface when multiple add-ons are activated.
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
		global $give_addons, $pagenow;

		// Append add-on information to the global variable.
		$give_addons[] = $_banner_details;

		if ( 'plugins.php' === $pagenow ) {

			// Get the current user.
			$current_user  = wp_get_current_user();
			$this->user_id = $current_user->ID;

			// Set up hooks.
			$this->init();

			// Store user id who activated plugin.
			$this->add_addon_activate_meta();

			// Check if notice callback is already hooked.
			if ( ! $this->is_banner_notice_hooked() ) {
				// If multiple add-on are activated then show activation banner in tab view.
				add_action( 'admin_notices', array( $this, 'addon_activation_banner_notices' ), 10 );
			}
		}
	}

	/**
	 * Get the meta key name.
	 *
	 * @since 2.1
	 * @param string $addon_banner_key
	 *
	 * @return string
	 */
	public static function get_banner_user_meta_key( $addon_banner_key ) {
		$addon_slug = sanitize_text_field( $addon_banner_key );

		return "give_addon_{$addon_slug}_active_by_user";
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
		// Get the current page to add the notice to
		add_action( 'current_screen', array( $this, 'give_addon_notice_ignore' ) );

		// Get the Give add-ons.
		$give_addons = $this->get_plugin_file_names();

		if ( ! empty( $give_addons ) ) {

			// Go through each of the add-on and hook deactivate action.
			foreach ( $give_addons as $addon_name => $give_addon ) {

				// Testing?
				if ( true === $give_addon['testing'] ) {
					$nag_meta_key = 'give_addon_activation_ignore_' . $addon_name;
					delete_user_meta( $this->user_id, $nag_meta_key );
				}

				// Add deactivate hook.
				add_action( 'deactivate_' . $give_addon['plugin_main_file'], array( $this, 'remove_addon_activate_meta' ) );
			}
		}
	}

	/**
	 * Get plugin file name.
	 *
	 * @since   1.8
	 * @access  private
	 * @return mixed
	 */
	private function get_plugin_file_names() {
		global $give_addons;

		// Get recently activated plugins.
		$active_plugins = get_option( 'active_plugins' );

		$file_names = array();

		if ( empty( $give_addons ) ) {
			return $file_names;
		}

		// Go through each addon and get the plugin file url.
		foreach ( $give_addons as $give_addon ) {
			$file_name = '';
			$file_path = explode( '/plugins/', $give_addon['file'] );
			if ( $file_path ) {
				$file_path = array_pop( $file_path );
				$file_name = current( explode( '/', $file_path ) );
			}

			if ( ! empty( $file_name ) ) {
				foreach ( $active_plugins as $plugin ) {
					if ( false !== strpos( $plugin, $file_name ) ) {
						$add_on_key                     = sanitize_title( $give_addon['name'] );
						$give_addon['plugin_main_file'] = $plugin; // Include plugin file.
						$file_names[ $add_on_key ]      = $give_addon;
						break;
					}
				}
			}
		}

		return $file_names;
	}

	/**
	 * Setup user id to option
	 *
	 * @since  1.8
	 * @access private
	 */
	private function add_addon_activate_meta() {
		// Get all activated add-ons.
		$give_addons = $this->get_plugin_file_names();

		if ( ! empty( $give_addons ) ) {

			// Go through each add-ons and add meta data.
			foreach ( $give_addons as $banner_addon_name => $addon ) {

				// User meta key.
				$user_id = __give_get_active_by_user_meta( $banner_addon_name );

				if ( ! $user_id ) {
					$option_key = self::get_banner_user_meta_key( $banner_addon_name );

					// store user id who activated add-on.
					update_option( $option_key, $this->user_id, false );

					// Update global cache.
					$GLOBALS['give_addon_activated_by_user'][$option_key] = $this->user_id;
				}
			}
		}
	}

	/**
	 * Check if the addon_activation_banner_notices function has already been hooked to admin_notice.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_banner_notice_hooked() {
		global $wp_filter;
		$notice_already_hooked = false;

		if ( isset( $wp_filter['admin_notices']->callbacks[10] ) ) {
			// Get all of the hooks.
			$admin_notice_callbacks = array_keys( $wp_filter['admin_notices']->callbacks[10] );

			if ( ! empty( $admin_notice_callbacks ) ) {
				foreach ( $admin_notice_callbacks as $key ) {
					//If the key is found in your string, set $found to true
					if ( false !== strpos( $key, 'addon_activation_banner_notices' ) ) {
						$notice_already_hooked = true;
					}
				}
			}
		}

		return $notice_already_hooked;
	}

	/**
	 * Get the add-on banner notices.
	 *
	 * @since 2.1.0
	 */
	public function addon_activation_banner_notices() {
		global $pagenow, $give_addons;

		// Bailout.
		if ( 'plugins.php' !== $pagenow ) {
			return false;
		}

		// Store the add-ons of which activation banner should be shown.
		$addon_to_display = array();

		// Get recently activated add-ons.
		$recent_activated = $this->get_recently_activated_addons();
		$latest_addon     = array();

		// Get the plugin folder name, because many give-addon not sending proper plugin_file.
		if ( ! empty( $recent_activated ) ) {
			foreach ( $recent_activated as $recent_addon ) {
				// Get the add-on folder name.
				$latest_addon[] = substr( $recent_addon, 0, strpos( $recent_addon, '/' ) );
			}
		}

		// Go through each of the give add-on.
		foreach ( $give_addons as $addon ) {
			$addon_sanitized_name = sanitize_title( $addon['name'] );

			// Get the add-on dismiss status.
			$add_on_state = get_user_meta( $this->user_id, "give_addon_activation_ignore_{$addon_sanitized_name}", true );

			// Get the option key.
			$activate_by_user = (int) __give_get_active_by_user_meta( $addon_sanitized_name );

			// Remove plugin file and get the Add-on's folder name only.
			$file_path = $this->get_plugin_folder_name( $addon['file'] );

			// If add-on were never dismissed.
			if ( 'true' !== $add_on_state && $this->user_id === $activate_by_user ) {
				if ( ! empty( $latest_addon ) && ( in_array( $file_path, $latest_addon, true ) || empty( $latest_addon ) ) ) {
					$addon_to_display[] = $addon;
				}
			}
		}

		if ( ! empty( $addon_to_display ) ) {
			ob_start();

			// Output inline styles here because there's no reason
			// to enqueued them after the alert is dismissed.
			$this->print_css_js();
			?>
			<div class="<?php echo ( 1 !== count( $addon_to_display ) ) ? 'give-alert-tab-wrapper' : ''; ?> updated give-addon-alert give-notice">
				<?php
				// If multiple add-on are activated.
				if ( 1 !== count( $addon_to_display ) ) {
					?>
					<div class="give-vertical-tab">
						<div class="give-addon-tab-list">
							<ul class="give-alert-addon-list">
								<?php
								$is_first = true;
								foreach ( $addon_to_display as $banner ) {
									?>
									<li class="give-tab-list<?php echo ( true === $is_first ) ? ' active' : ''; ?>" id="give-addon-<?php echo esc_attr( basename( $banner['file'], '.php' ) ); ?>">
										<a href="#"><?php echo esc_html( $banner['name'] ); ?></a>
									</li>
									<?php
									$is_first = false;
								}
								$is_first = true;
								?>
							</ul>
						</div>
						<div class="give-right-side-block">
							<?php foreach ( $addon_to_display as $banner ) : ?>
								<div class="give-tab-details <?php echo ( true === $is_first ) ? ' active' : ''; ?> " id="give-addon-<?php echo esc_attr( basename( $banner['file'], '.php' ) ); ?>">
									<?php
										// Render single add banner.
										$this->render_single_addon_banner( $banner, false );
										$is_first = false;
									?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
				} else {
					$this->render_single_addon_banner( $addon_to_display[0], true );
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
	 * Render single banner activation
	 *
	 * @since 2.1.0
	 *
	 * @param array $banner_arr Banner options.
	 * @param bool  $is_single  Is single.
	 */
	private function render_single_addon_banner( $banner_arr, $is_single ) {
		// Get all give add-on.
		$give_addons = give_get_plugins();

		// Plugin main file.
		$plugin_file = $banner_arr['file'];

		// Get the plugin main file.
		foreach ( $give_addons as $main_file => $addon ) {
			// Plugin should be activated.
			if ( ! is_plugin_active( $main_file ) ) {
				continue;
			}

			if (
				isset( $banner_arr['name'] )
				&& 'add-on' === $addon['Type']
				&& $this->get_plugin_folder_name( $main_file ) === $this->get_plugin_folder_name( $plugin_file )
			) {
				$plugin_file = WP_PLUGIN_DIR . '/' . $main_file;
				break;
			}
		}

		// Create dismiss URL.
		$dismiss_url = $is_single
			? admin_url( 'plugins.php?give_addon_activation_ignore=1&give_addon=' . sanitize_title( $banner_arr['name'] ) )
			: admin_url( 'plugins.php?give_addon_activation_ignore=1&give_addon=all' );

		// Get the add-on details.
		$plugin_data = get_plugin_data( $plugin_file );
		?>
		<img src="<?php echo esc_url( GIVE_PLUGIN_URL . 'assets/dist/images/give-icon-full-circle.svg' ); ?>" class="give-logo" />
		<div class="give-alert-message">
			<h3>
				<?php
				printf(
					/* translators: %s: Add-on name */
					'%s<span>%s</span>',
					__( 'New GiveWP Add-on Activated: ', 'give' ),
					esc_html( $banner_arr['name'] )
				);
				?>
			</h3>
			<a href="<?php echo esc_url( $dismiss_url ); ?>" class="dismiss">
				<span class="dashicons dashicons-dismiss"></span>
			</a>
			<div class="alert-actions">
				<?php
				//Point them to your settings page.
				if ( ! empty( $plugin_data['Description'] ) ) {
					?>
					<span class="give-addon-description">
					<em><?php echo esc_html( strip_tags( $plugin_data['Description'] ) ); ?></em></span><br />
					<?php
				}
				if ( isset( $banner_arr['settings_url'] ) ) {
					printf(
						'<a href="%s"><span class="dashicons dashicons-admin-settings"></span>%s</a>',
						esc_url( $banner_arr['settings_url'] ),
						esc_html__( 'Go to Settings', 'give' )
					);
				}
				// Show them how to configure the Addon.
				if ( isset( $banner_arr['documentation_url'] ) ) {
					printf(
						'<a href="%s" target="_blank"><span class="dashicons dashicons-media-text"></span>%s</a>',
						esc_url( $banner_arr['documentation_url'] ),
						sprintf(
							/* translators: %s: Add-on name */
							esc_html__( 'Documentation: %s Add-on', 'give' ),
							esc_html( $banner_arr['name'] )
						)
					);
				}

				//Let them signup for plugin updates
				if ( isset( $banner_arr['support_url'] ) ) {
					printf(
						'<a href="%s" target="_blank"><span class="dashicons dashicons-sos"></span>%s</a>',
						esc_url( $banner_arr['support_url'] ),
						esc_html__( 'Get Support', 'give' )
					);
				}
				?>
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
		/**
		 * If user clicks to ignore the notice, add that to their user meta the banner then checks whether this tag exists already or not.
		 * See here: http://codex.wordpress.org/Function_Reference/add_user_meta
		 */
		if (
			isset( $_GET['give_addon'], $_GET['give_addon_activation_ignore'] )
			&& '1' === $_GET['give_addon_activation_ignore']
		) {
			// Get the value of the 'give_addon' query string.
			$addon_query_arg    = sanitize_text_field( wp_unslash( $_GET['give_addon'] ) );
			$deactivated_addons = array();

			// If All add-on requested to dismiss.
			if ( 'all' === $addon_query_arg ) {
				// Get all activated add-ons.
				$give_addons = $this->get_plugin_file_names();

				if ( ! empty( $give_addons ) ) {
					$deactivated_addons = array_keys( $give_addons );
				}
			} else {
				// Store the addon to deactivate.
				$deactivated_addons[] = $addon_query_arg;
			}

			if ( ! empty( $deactivated_addons ) ) {
				foreach ( $deactivated_addons as $addon ) {
					// Record it user meta.
					add_user_meta( $this->user_id, "give_addon_activation_ignore_{$addon}", 'true', true );
				}
			}
		}
	}

	/**
	 * Delete user id from option if plugin deactivated.
	 *
	 * @since  1.8
	 * @since  2.1.0 Added support for multiple addons.
	 * @access public
	 */
	public function remove_addon_activate_meta() {
		// Get the hook name and then grab the plugin file from it.
		$plugin_file = str_replace( 'deactivate_', '', current_action() );

		// Get all activated add-ons.
		$give_addons = $this->get_plugin_file_names();

		if ( ! empty( $give_addons ) ) {
			foreach ( $give_addons as $banner_addon_name => $addon ) {
				if ( $plugin_file === $addon['plugin_main_file'] ) {

					// Get the user meta key.
					$user_id = (int) __give_get_active_by_user_meta( $banner_addon_name );

					if ( $user_id ) {
						// Get user meta for this add-on.
						$nag_meta_key = "give_addon_activation_ignore_{$banner_addon_name}";

						// Delete plugin activation option key.
						delete_option( self::get_banner_user_meta_key( $banner_addon_name ) );
						// Delete user meta of plugin activation.
						delete_user_meta( $user_id, $nag_meta_key );
					}
				}
			}
		}
	}

	/**
	 * Get list of add-on last activated.
	 *
	 * @since 2.1.0
	 *
	 * @return mixed|array
	 */
	public function get_recently_activated_addons() {
		return give_get_recently_activated_addons();
	}

	/**
	 * Get the addon's folder name.
	 *
	 * @since 2.1.0
	 *
	 * @param string $main_file Plugin Main File.
	 *
	 * @return bool|mixed|string
	 */
	public function get_plugin_folder_name( $main_file ) {
		// Remove plugin file and get the Add-on's folder name only.
		$file_path       = explode( '/plugins/', $main_file );
		$addon_file_path = array_pop( $file_path );
		$addon_file_path = substr( $addon_file_path, 0, strpos( $addon_file_path, '/' ) );

		return $addon_file_path;
	}

	/**
	 * Add activation banner css and js .
	 *
	 * @since  1.8.16
	 * @since  2.1.0 Added JS code for multiple add-on.
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

			div.give-alert-tab-wrapper .dismiss {
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
				max-height: 146px;
				overflow: hidden;
			}

			div.give-addon-alert .give-addon-description {
				padding: 1px;
				display: inline-block;
				color: #777;
				margin-bottom: 12px;
			}

			div.give-alert-tab-wrapper .give-right-side-block {
				width: calc(100% - 250px);
				display: inline-block;
				float: left;
				background: #fff;
				position: relative;
			}

			div.give-vertical-tab {
				width: 100%;
			}

			ul.give-alert-addon-list li {
				display: block;
				border: 1px solid #d1d1d18f;
				border-width: 1px 0px 0px 0px;
				margin: 0;
			}

			ul.give-alert-addon-list li a.inactivate {
				cursor: default;
			}

			ul.give-alert-addon-list li a {
				display: block;
				font-weight: bold;
				color: #a3a3a3;
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

			div.updated.give-addon-alert.give-notice.give-alert-tab-wrapper {
				display: inline-block;
				width: 100%;
			}

			.give-alert-tab-wrapper .give-tab-details {
				display: none;
				min-height: 100px;
				position: absolute;
				top: 0;
				left: 0;
				padding: 20px 20px 20px 40px;
			}

			.give-alert-tab-wrapper .give-tab-details.active {
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

			ul.give-alert-addon-list::-webkit-scrollbar {
				height: 10px;
				width: 10px;
				border-radius: 4px;
				transition: all 0.3s ease;
				background: rgba(158, 158, 158, 0.15);
			}

			ul.give-alert-addon-list::-webkit-scrollbar-thumb {
				background: #939395;
				border-radius: 4px;
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

		<!-- Start of the Give Add-on tab JS -->
		<script type="text/javascript">
					jQuery( document ).ready( function( $ ) {
						$( '.give-alert-tab-wrapper' ).on( 'click', '.give-tab-list', function() {
							if ( $( this ).find( 'a' ).hasClass( 'inactivate' ) ) {
								return false;
							}

							var
								clicked_tab = $( this ).attr( 'id' ),
								addon_tab_wrapper = $( this ).closest( '.give-alert-tab-wrapper' );

							// Remove 'active' class from all tab list.
							$( '.give-alert-addon-list li' ).removeClass( 'active' );
							// Add active class to the selected tab.
							$( this ).addClass( 'active' );
							// Remove 'active' class from the details.
							addon_tab_wrapper.find( '.give-tab-details' ).removeClass( 'active' );
							addon_tab_wrapper.find( '.give-right-side-block .give-tab-details#' + clicked_tab ).addClass( 'active' );

							return false;
						} );

						var add_on_tabs = $( '.give-alert-addon-list' );

						add_on_tabs
							.mouseout( function() {
								$( this ).css( 'overflow', 'hidden' );
							} )
							.mouseover( function() {
								$( this ).css( 'overflow', 'auto' );
							} );

						// Prevent default click event of the add-on.
						add_on_tabs.find( 'li a' ).on( 'click', function( e ) {
							e.preventDefault();
						} );

						// If total length of the add-on is 2.
						if ( 2 === add_on_tabs.find( 'li' ).length ) {
							var li = $( 'li.give-tab-list' );
							li.last().clone().prependTo( 'ul.give-alert-addon-list' );
							li.last().removeAttr( 'id' ).find( 'a' ).addClass( 'inactivate' ).html( '&nbsp;' );
							$( '.give-tab-list:first' ).trigger( 'click' );
						}
					} );
		</script>
		<!-- End of the Give Add-on tab JS -->
		<?php
	}
}
