<?php
/**
 * Admin View: Import Core Settings
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
 * @since 1.8.17
 */
do_action( 'give_tools_import_core_settings_main_before' );
?>
	<div id="poststuff" class="give-clearfix">
		<div class="postbox">
			<h1 class="give-importer-h1" align="center"><?php esc_html_e( 'Import Settings', 'give' ); ?></h1>
			<div class="inside give-tools-setting-page-import give-import-core-settings">
				<?php
				/**
				 * Fires before from start.
				 *
				 * @since 1.8.17
				 */
				do_action( 'give_tools_import_core_settings_form_before_start' );
				?>
				<form method="post" id="give-import-core-settings-form"
					  class="give-import-form tools-setting-page-import tools-setting-page-import"
					  enctype="multipart/form-data">

					<?php
					/**
					 * Fires just after form start.
					 *
					 * @since 1.8.17
					 */
					do_action( 'give_tools_import_core_settings_form_start' );
					?>

					<?php
					/**
					 * Fires just after before form end.
					 *
					 * @since 1.8.17
					 */
					do_action( 'give_tools_import_core_settings_form_end' );
					?>
				</form>
				<?php
				/**
				 * Fires just after form end.
				 *
				 * @since 1.8.17
				 */
				do_action( 'give_tools_import_core_settings_form_after_end' );
				?>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- #poststuff -->
<?php
/**
 * Fires after displaying the import div tools.
 *
 * @since 1.8.17
 */
do_action( 'give_tools_import_core_settings_main_after' );
