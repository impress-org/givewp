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

				<table class="widefat import-options-table give-table">
					<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Import Type', 'give' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Import Options', 'give' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					/**
					 * Fires in the reports import tab.
					 *
					 * Allows you to add new TR elements to the table before
					 * other elements.
					 *
					 * @since 1.0
					 */
					do_action( 'give_tools_tab_import_table_top' );
					?>
					<tr class="give-import-core-settings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Import Give core settings', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Import Give\'s core settings in JSON format.', 'give' ); ?></p>
						</td>
						<td>
							<form id="core-settings-importer-form" method="post" enctype="multipart/form-data">
								<input type="hidden" name="give-action"
								       value="core_settings_import"/>
								<input type="file" name="json_file">
								<input type="submit">
							</form>
						</td>
					</tr>
					<?php
					/**
					 * Fires in the reports export tab.
					 *
					 * Allows you to add new TR elements to the table after
					 * other elements.
					 *
					 * @since 1.0
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
