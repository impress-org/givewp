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
do_action( 'give_tools_import_main_before' );
?>
    <div id="poststuff">
        <div class="postbox">
            <h1 class="handle ui-sortable-handle" align="center"><?php esc_html_e( 'Import Donations', 'give' ); ?></h1>
            <div class="inside recount-stats-controls give-tools-setting-page-import">
				<?php
				/**
				 * Fires before from start.
				 *
				 * @since 1.5
				 */
				do_action( 'give_tools_import_form_before_start' );
				?>
                <form method="post" id="give-tools-recount-form" class="give-import-form tools-setting-page-import tools-setting-page-import">

					<?php
					/**
					 * Fires just after form start.
					 *
					 * @since 1.5
					 */
					do_action( 'give_tools_import_form_start' );
					?>

					<?php
					/**
					 * Fires just after before form end.
					 *
					 * @since 1.5
					 */
					do_action( 'give_tools_import_form_end' );
					?>
                </form>
				<?php
				/**
				 * Fires just after form end.
				 *
				 * @since 1.5
				 */
				do_action( 'give_tools_import_form_after_end' );
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
do_action( 'give_tools_import_main_after' );
