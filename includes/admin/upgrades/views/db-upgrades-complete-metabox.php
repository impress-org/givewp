<div id="give-db-updates" data-resume-update="0">
	<div class="postbox-container">
		<div class="postbox">
			<h2 class="hndle"><?php esc_html_e( 'Database Updates', 'give' ); ?></h2>
			<div class="inside">
				<div class="progress-container">
					<p class="update-message"><strong><?php esc_html_e( 'Updates Completed.', 'give' ); ?></strong></p>
					<div class="progress-content">
						<div class="notice-wrap give-clearfix">
							<div class="notice notice-success is-dismissible inline">
								<p><?php esc_html_e( 'GiveWP database updates completed successfully. Thank you for updating to the latest version!', 'give' ); ?>
								</p>
								<button type="button" class="notice-dismiss"></button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- .inside -->
		</div><!-- .postbox -->
	</div>
</div>
<?php delete_option( 'give_show_db_upgrade_complete_notice' ); ?>
