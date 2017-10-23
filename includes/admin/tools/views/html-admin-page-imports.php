<?php
/**
 * Admin View: Imports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div id="poststuff">
	<div id="give-dashboard-widgets-wrap">
		<div id="post-body">
			<div id="post-body-content">

				<?php
				/**
				 * Fires before the reports Import tab.
				 *
				 * @since 1.8.14
				 */
				do_action( 'give_tools_tab_import_content_top' );
				?>

				<table class="widefat Import-options-table give-table">
					<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Import Type', 'give' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Import Options', 'give' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					/**
					 * Fires in the reports Import tab.
					 *
					 * Allows you to add new TR elements to the table before
					 * other elements.
					 *
					 * @since 1.8.14
					 */
					do_action( 'give_tools_tab_import_table_top' );
					?>

					<tr class="give-Import-pdf-sales-earnings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Import Donations', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Import a CSV of Donations.', 'give' ); ?></p>
						</td>
						<td>
							<a class="button" href="<?php echo add_query_arg( array( 'importer-type' => 'import_donations' ) ); ?>">
								<?php esc_html_e( 'Import CSV', 'give' ); ?>
							</a>
						</td>
					</tr>

					<tr class="give-import-core-settings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Import Give core settings', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Import Give\'s core settings in JSON format.', 'give' ); ?></p>
						</td>
						<td>
							<form id="give-core-settings-importer-form" method="post" enctype="multipart/form-data">
								<?php
								$type = (string) ( ! empty( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'merge' );
								?>
								<p class="give-core-settings-type">
									<span>
										<label for="give-core-settings-type-merge">
											<span><?php esc_html_e( 'Merge:', 'give' ); ?></span>
											<input type="radio" value="merge" <?php echo ( 'merge' === $type ? 'checked' : '' ); ?> name="type" class="give-core-settings-type-merge" id="give-core-settings-type-merge">
										</label>
									</span>

									<span>
										<label for="give-core-settings-type-replace">
											<span><?php esc_html_e( 'Replace:', 'give' ); ?></span>
											<input type="radio" value="replace" <?php echo ( 'replace' === $type ? 'checked' : '' ); ?> name="type" class="give-core-settings-type-replace" id="give-core-settings-type-replace">
										</label>
									</span>
								</p>

								<input type="hidden" name="give-action" value="core_settings_import"/>

								<input type="file" name="json_file">

								<input type="submit" class="button-secondary" <?php esc_html_e( 'Name', 'give' ); ?>>
							</form>
						</td>
					</tr>

					<?php
					/**
					 * Fires in the reports Import tab.
					 *
					 * Allows you to add new TR elements to the table after
					 * other elements.
					 *
					 * @since 1.8.14
					 */
					do_action( 'give_tools_tab_import_table_bottom' );
					?>
					</tbody>
				</table>

				<?php
				/**
				 * Fires after the reports Import tab.
				 *
				 * @since 1.8.14
				 */
				do_action( 'give_tools_tab_import_content_bottom' );
				?>

			</div>
			<!-- .post-body-content -->
		</div>
		<!-- .post-body -->
	</div><!-- #give-dashboard-widgets-wrap -->
</div><!-- #poststuff -->
