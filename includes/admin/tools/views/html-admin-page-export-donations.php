<?php
/**
 * Admin View: Export Donation
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
 * @since 2.1
 */
do_action( 'give_tools_export_donations_main_before' );
?>
	<div id="poststuff">
		<div class="postbox">
			<h1 class="give-export-h1" align="center"><?php esc_html_e( 'Export Donations', 'give' ); ?></h1>
			<div class="inside give-tools-setting-page-export give-export_donations">
				<?php
				/**
				 * Fires before from start.
				 *
				 * @since 2.1
				 */
				do_action( 'give_tools_export_donations_form_before_start' );
				?>
				<form method="post" id="give-export_donations-form"
				      class="give-export-form tools-setting-page-export tools-setting-page-export"
				      enctype="multipart/form-data">

					<?php
					/**
					 * Fires just after form start.
					 *
					 * @since 2.1
					 */
					do_action( 'give_tools_export_donations_form_start' );
					?>

					<?php
					/**
					 * Fires just after before form end.
					 *
					 * @since 2.1
					 */
					do_action( 'give_tools_export_donations_form_end' );
					?>
				</form>
				<?php
				/**
				 * Fires just after form end.
				 *
				 * @since 2.1
				 */
				do_action( 'give_tools_iexport_donations_form_after_end' );
				?>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- #poststuff -->
<?php
/**
 * Fires after displaying the import div tools.
 *
 * @since 2.1
 */
do_action( 'give_tools_export_donations_main_after' );
