<?php
/**
 * Upgrade Screen
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.12
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$give_updates = Give_Updates::get_instance();
?>
<div class="wrap" id="poststuff">
	<div id="give-updates">
		<h1 id="give-updates-h1"><?php esc_html_e( 'Give - Updates', 'give' ); ?></h1>
		<div class="give-update-panel-content">
			<p><?php printf( __( 'Give regularly receives new features, bug fixes, and enhancements. It is important to always stay up-to-date with latest version of Give core and its add-ons.  Please create a backup of your site before updating. To update add-ons be sure your <a href="%1$s">license keys</a> are activated.', 'give' ), 'https://givewp.com/my-account/' ); ?></p>
		</div>

		<?php $update_counter = 1; ?>

		<?php $db_updates = $give_updates->get_db_update_count(); ?>
		<?php if ( ! empty( $db_updates ) ) : ?>
			<?php $db_update_url = add_query_arg( array(
				'type' => 'database',
			) ); ?>
			<div id="give-db-updates">
				<div class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e( 'Database Updates', 'give' ); ?></h2>
						<div class="inside">
							<div class="panel-content">
								<p><?php echo sprintf( __( 'Give needs to update the database. <a href="%s">Update now ></a>', 'give' ), $db_update_url ); ?></p>
							</div>
							<div class="progress-container give-hidden">
								<p class="update-message" data-update-count="<?php echo $db_updates; ?>"
								   data-resume-update="<?php echo $give_updates->resume_updates(); ?>">
									<strong><?php echo sprintf( __( 'Update 1 of %s', 'give' ), $db_updates ); ?></strong></p>
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
			<?php $plugin_update_url = add_query_arg( array(
				's' => 'Give',
			), admin_url( '/plugins.php' ) ); ?>
			<div id="give-plugin-updates">
				<div class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e( 'Add-on Updates', 'give' ); ?></h2>
						<div class="inside">
							<div class="panel-content">
								<p><?php echo sprintf( __( 'There %1$s %2$s Give %3$s that %4$s to be updated. <a href="%5$s">Update now ></a>', 'give' ), _n( 'is', 'are', $plugin_updates, 'give' ), $plugin_updates, _n( 'add-on', 'add-ons', $plugin_updates, 'give' ), _n( 'needs', 'need', $plugin_updates, 'give' ), $plugin_update_url  ); ?></p>
								<?php include_once 'plugins-update-section.php'; ?>
							</div>
						</div>
						<!-- .inside -->
					</div><!-- .postbox -->
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>