<?php
/**
 * Give Activation Banner
 *
 * @author  WordImpress
 * @version 1.0
 * https://github.com/WordImpress/Give-Activation-Banner
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Addon_Activation_Banner {

	/**
	 *
	 * Class constructor.
	 *
	 * @uses plugin_basename()
	 * @uses hook()
	 *
	 * @param $_banner_details
	 */
	function __construct( $_banner_details ) {

		global $current_user;
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
	 * @uses add_filter()
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
	 */
	public function give_addon_activation_admin_notice() {
		global $pagenow;

		//Make sure we're on the plugins page.
		if ( $pagenow !== 'plugins.php' ) {
			return false;
		}

		// If the user hasn't already dismissed our alert,
		// Output the activation banner
		if ( ! get_user_meta( $this->user_id, $this->nag_meta_key ) ) { ?>

			<!-- * I output inline styles here
				 * because there's no reason to keep these
				 * enqueued after the alert is dismissed. -->
			<style>
				div.give-addon-alert.updated {
					padding: 1em 2em;
					position: relative;
					border-color: #66BB6A;
				}

				div.give-addon-alert img {
					max-width: 50px;
					position: relative;
					top: 1em;
				}

				div.give-addon-alert h3 {
					display: inline;
					position: relative;
					top: -20px;
					left: 20px;
					font-size: 24px;
					font-weight: 300;
				}

				div.give-addon-alert h3 span {
					font-weight: 900;
					color: #66BB6A;
				}

				div.give-addon-alert .alert-actions {
					position: relative;
					left: 70px;
					top: -10px;

				}

				div.give-addon-alert a {
					color: #66BB6A;
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
					right: 0;
					height: 100%;
					top: 50%;
					margin-top: -10px;
					outline: none;
					box-shadow: none;
				}
			</style>

			<!-- * Now we output the HTML
				 * of the banner 			-->

			<div class="updated give-addon-alert">

				<!-- Your Logo -->
				<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/images/svg/give-icon-full-circle.svg" class="give-logo" />

				<!-- Your Message -->
				<h3><?php echo sprintf( __( 'Thank you for installing Give\'s %1$s%2$s%3$s Add-on!', 'give' ), '<span>', $this->banner_details['name'], '</span>' ); ?></h3>

				<a href="<?php
				//The Dismiss Button
				$nag_admin_dismiss_url = 'plugins.php?' . $this->nag_meta_key . '=0';
				echo admin_url( $nag_admin_dismiss_url ); ?>" class="dismiss"><span class="dashicons dashicons-dismiss"></span></a>

				<!-- * Now we output a few "actions"
					 * that the user can take from here -->

				<div class="alert-actions">

					<?php //Point them to your settings page
					if ( isset( $this->banner_details['settings_url'] ) ) { ?>
						<a href="<?php echo $this->banner_details['settings_url']; ?>">
							<span class="dashicons dashicons-admin-settings"></span><?php _e( 'Go to Settings', 'give' ); ?>
						</a>
					<?php } ?>

					<?php
					// Show them how to configure the Addon
					if ( isset( $this->banner_details['documentation_url'] ) ) { ?>
						<a href="<?php echo $this->banner_details['documentation_url'] ?>" target="_blank"><span class="dashicons dashicons-media-text"></span><?php echo sprintf( __( 'Documentation: %1$s Add-on', 'give' ), $this->banner_details['name'] ); ?>
						</a>
					<?php } ?>
					<?php
					//Let them signup for plugin updates
					if ( isset( $this->banner_details['support_url'] ) ) { ?>

						<a href="<?php echo $this->banner_details['support_url'] ?>" target="_blank">
							<span class="dashicons dashicons-sos"></span><?php _e( 'Get Support', 'give' ); ?>
						</a>

					<?php } ?>

				</div>
			</div>
			<?php
		}
	}


	/**
	 * Ignore Nag
	 * @description: This is the action that allows the user to dismiss the banner it basically sets a tag to their user meta data
	 */
	public function give_addon_notice_ignore() {

		/* If user clicks to ignore the notice, add that to their user meta the banner then checks whether this tag exists already or not.
		 * See here: http://codex.wordpress.org/Function_Reference/add_user_meta
		 */
		if ( isset( $_GET[ $this->nag_meta_key ] ) && '0' == $_GET[ $this->nag_meta_key ] ) {

			//Get the global user
			global $current_user;
			$user_id = $current_user->ID;

			add_user_meta( $user_id, $this->nag_meta_key, 'true', true );
		}
	}

}

