<?php
/**
 * Admin View: Import Donations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_give_settings' ) ) {
	return;
}

/**
 * Fires before displaying the import div tools.
 *
 * @since 1.8.13
 */
do_action( 'give_tools_import_donations_main_before' );
?>
	<div id="poststuff" class="give-clearfix">
		<div class="postbox">
			<h1 class="give-importer-h1" align="center">
				<?php
				_e( 'Import Donations', 'give' );

				if ( ! empty( $_POST['mapto'] ) && ! empty( $_GET['dry_run'] ) ) {
					printf(
						'<strong> %s</strong>',
						__( '(Dry Run)', 'give' )
					);
				}
				?>
			</h1>
			<div class="inside give-tools-setting-page-import give-import-donations">
				<?php
				/**
				 * Fires before from start.
				 *
				 * @since 1.8.14
				 */
				do_action( 'give_tools_import_donations_form_before_start' );
				?>
				<form method="post" id="give-import-donations-form" class="give-import-form tools-setting-page-import tools-setting-page-import">

					<?php
					/**
					 * Fires just after form start.
					 *
					 * @since 1.8.14
					 */
					do_action( 'give_tools_import_donations_form_start' );
					?>

					<?php
					/**
					 * Fires just after before form end.
					 *
					 * @since 1.8.14
					 */
					do_action( 'give_tools_import_donations_form_end' );
					?>
				</form>
				<?php
				/**
				 * Fires just after form end.
				 *
				 * @since 1.8.14
				 */
				do_action( 'give_tools_import_donations_form_after_end' );
				?>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- #poststuff -->
<?php
/**
 * Fires after displaying the import div tools.
 *
 * @since 1.8.13
 */
do_action( 'give_tools_import_donations_main_after' );
