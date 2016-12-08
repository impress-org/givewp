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
	 * @param $_banner_details
	 */
	function __construct( $_banner_details ) {

		$current_user = wp_get_current_user();

		$this->banner_details = $_banner_details;
		$this->test_mode      = ( $this->banner_details['testing'] == 'true' ) ? true : false;
		$this->nag_meta_key   = 'give_addon_activation_ignore_' . sanitize_title( $this->banner_details['name'] );

		//Get current user
		$this->user_id = $current_user->ID;

		// Set up hooks.
		$this->init();
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

	}

	/**
	 * Give Addon Activation Banner
	 *
	 * @since  1.0
	 * @access public
	 */
	public function give_addon_activation_admin_notice() {
		$screen = get_current_screen();

		//Make sure we're on the plugins page.
		if ( $screen->parent_file !== 'plugins.php' ) {
			return false;
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
								<span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'Go to Settings', 'give' ); ?></a>
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
			$user_id = $current_user->ID;

			add_user_meta( $user_id, $this->nag_meta_key, 'true', true );
		}
	}

}