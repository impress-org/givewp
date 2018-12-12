<?php
/**
 * Upgrade Screen
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2017, GiveWP
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
		<hr class="wp-header-end">

		<?php $db_updates = $give_updates->get_pending_db_update_count(); ?>
		<?php if ( ! empty( $db_updates ) ) : ?>
			<?php
			$is_doing_updates = $give_updates->is_doing_updates();
			$db_update_url    = add_query_arg( array( 'type' => 'database', ) );
			$resume_updates   = get_option( 'give_doing_upgrade' );
			$width            = ! empty( $resume_updates ) ? $resume_updates['percentage'] : 0;
			?>
			<div class="give-update-panel-content">
				<p><?php printf( __( 'Give regularly receives new features, bug fixes, and enhancements. It is important to always stay up-to-date with latest version of Give core and its add-ons.  Please create a backup of your site before updating. To update add-ons be sure your <a href="%1$s">license keys</a> are activated.', 'give' ), 'https://givewp.com/my-account/' ); ?></p>
			</div>

			<div id="give-db-updates" data-resume-update="<?php echo absint( $give_updates->is_doing_updates() ); ?>">
				<div class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e( 'Database Updates', 'give' ); ?></h2>
						<div class="inside">
							<div class="panel-content">
								<p class="give-update-button">
									<?php
									if( ! give_test_ajax_works() ) {
										echo sprintf(
											'<div class="notice notice-warning inline"><p>%s</p></div>',
											__( 'Give is currently updating the database. Please do not refresh or leave this page while the update is in progress.', 'give' )
										);
									}
									?>
									<span class="give-doing-update-text-p" <?php echo Give_Updates::$background_updater->is_paused_process() ? 'style="display:none;"' : ''; ?>>
										<?php
										echo sprintf(
											__( '%1$s <a href="%2$s" class="give-update-now %3$s">%4$s</a>', 'give' ),
											$is_doing_updates
												? sprintf(
													'%s%s',
													__( 'Give is currently updating the database', 'give' ),
													give_test_ajax_works() ?  ' ' . __( 'in the background.', 'give' ) : '.'
												)
												: __( 'Give needs to update the database.', 'give' ),
											$db_update_url,
											( $is_doing_updates ? 'give-hidden' : '' ),
											__( 'Update now', 'give' )
										);
										?>
									</span>
									<span class="give-update-paused-text-p" <?php echo ! Give_Updates::$background_updater->is_paused_process() ? 'style="display:none;"' : ''; ?>>
										<?php if ( get_option( 'give_upgrade_error' ) ) : ?>
											&nbsp<?php _e( 'An unexpected issue occurred during the database update which caused it to stop automatically. Please contact support for assistance.', 'give' ); ?>
										<?php else : ?>
											<?php _e( 'The updates have been paused.', 'give' ); ?>
										<?php endif; ?>
									</span>

									<?php if ( Give_Updates::$background_updater->is_paused_process() ) : ?>
										<?php $is_disabled = isset( $_GET['give-restart-db-upgrades'] ) ? ' disabled' : ''; ?>
										<button id="give-restart-upgrades" class="button button-primary alignright"
										        data-redirect-url="<?php echo esc_url( admin_url( '/edit.php?post_type=give_forms&page=give-updates&give-restart-db-upgrades=1' ) ); ?>"<?php echo $is_disabled; ?>><?php _e( 'Restart Upgrades', 'give' ); ?></button>
									<?php elseif ( $give_updates->is_doing_updates() ): ?>
										<?php $is_disabled = isset( $_GET['give-pause-db-upgrades'] ) ? ' disabled' : ''; ?>
										<button id="give-pause-upgrades" class="button button-primary alignright"
										        data-redirect-url="<?php echo esc_url( admin_url( '/edit.php?post_type=give_forms&page=give-updates&give-pause-db-upgrades=1' ) ); ?>"<?php echo $is_disabled; ?>>
											<?php _e( 'Pause Upgrades', 'give' ); ?>
										</button>
									<?php endif; ?>
								</p>
							</div>
							<div class="progress-container<?php echo $is_doing_updates ? '' : ' give-hidden'; ?>">
								<p class="update-message">
									<strong>
										<?php
										echo sprintf(
											__( 'Update %s of %s', 'give' ),
											$give_updates->get_running_db_update(),
											$give_updates->get_total_new_db_update_count()
										);
										?>
									</strong>
								</p>
								<div class="progress-content">
									<?php if ( $is_doing_updates ) : ?>
										<div class="notice-wrap give-clearfix">

											<?php if ( ! Give_Updates::$background_updater->is_paused_process() ) : ?>
												<span class="spinner is-active"></span>
											<?php endif; ?>

											<div class="give-progress">
												<div style="width: <?php echo $width ?>%;"></div>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
							<?php if ( ! $is_doing_updates ) : ?>
								<div class="give-run-database-update"></div>
							<?php endif; ?>
						</div>
						<!-- .inside -->
					</div><!-- .postbox -->
				</div> <!-- .post-container -->
			</div>
		<?php else: include GIVE_PLUGIN_DIR . 'includes/admin/upgrades/views/db-upgrades-complete-metabox.php'; ?>
		<?php endif; ?>

		<?php $plugin_updates = $give_updates->get_total_plugin_update_count(); ?>
		<?php if ( ! empty( $plugin_updates ) ) : ?>
			<?php $plugin_update_url = add_query_arg( array(
				'plugin_status' => 'give',
			), admin_url( '/plugins.php' ) ); ?>
			<div id="give-plugin-updates">
				<div class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e( 'Add-on Updates', 'give' ); ?></h2>
						<div class="inside">
							<div class="panel-content">
								<p>
									<?php
									printf(
										_n(
											'There is %1$d Give addon that needs to be updated. <a href="%2$s">Update now</a>',
											'There are %1$d Give addons that need to be updated. <a href="%2$s">Update now</a>',
											$plugin_updates,
											'give'
										),
										$plugin_updates,
										$plugin_update_url
									);
									?>
								</p>
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
