<?php
/**
 * Upgrade Screen
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$give_updates = Give_Updates::get_instance();
?>
<div class="wrap" id="poststuff">
	<div id="give-updates">
		<h1><?php esc_html_e( 'Give - Updates', 'give' ); ?></h1>
		<div class="postbox-container">
			<div class="postbox">
				<div class="inside">
					<div class="panel-content">
						<h2><?php _e( 'You have updates. Please <a href="%s">create a backup</a> of your site before updating. It is important to always stay up-to-date with latest versions of Give core and it\'s add-ons. We regularly release new features, bug fixes, and enhancements. To update add-ons be sure your <a href="%s">license keys</a> are active.', 'give' ); ?></h2>
					</div>
				</div>
				<!-- .inside -->
			</div><!-- .postbox -->
		</div>

		<?php $update_counter = 1; ?>

		<?php $db_updates = $give_updates->get_db_update_count(); ?>
		<?php if ( ! empty( $db_updates ) ) : ?>
			<?php $db_update_url = add_query_arg( array( 'type' => 'database' ) ); ?>
			<div id="give-db-updates">
				<div class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e( 'Database Updates', 'give' ); ?></h2>
						<div class="inside">
							<div class="panel-content">
								<p><?php echo sprintf( __( 'Give needs to update the database. <a href="%s">Update now ></a>', 'give' ), $db_update_url ); ?></p>
							</div>
							<div class="progress-container give-hidden">
								<strong class="update-message" data-update-count="<?php echo  $db_updates; ?>"  data-resume-update="<?php echo $give_updates->resume_updates(); ?>"><?php echo sprintf( __( 'Update 1 of %s', 'give' ), $db_updates ); ?></strong>
								<div class="progress-content"></div>
							</div>
						</div>
						<!-- .inside -->
					</div><!-- .postbox -->
				</div>
			</div>
		<?php endif; ?>

		<?php $plugin_updates = $give_updates->get_plugin_update_count(); ?>
		<?php if ( ! empty( $plugin_updates ) ) : ?>
			<?php $plugin_update_url = add_query_arg( array( 's' => 'Give' ), admin_url( '/plugins.php' ) ); ?>
			<div id="give-plugin-updates">
				<div class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e( 'Plugin Updates', 'give' ); ?></h2>
						<!--<h2 class="hndle"><span>-->
						<?php //echo sprintf( __( 'Update 1 of %s', 'give' ), $db_updates ); ?><!--</span></h2>-->
						<div class="inside">
							<div class="panel-content">
								<p><?php echo sprintf( __( 'There are %s Give %s that need to be updated. <a href="%s">Update now ></a>', 'give' ), $plugin_updates, _n( 'addon', 'addons', $plugin_updates, 'give' ), $plugin_update_url ); ?></p>
							</div>
						</div>
						<!-- .inside -->
					</div><!-- .postbox -->
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>