<?php
/**
 * Admin View: Import Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! current_user_can( 'manage_give_settings' ) ) {
    return;
}

do_action( 'give_tools_import_subscriptions_main_before' );
?>
    <div id="poststuff" class="give-clearfix">
        <div class="postbox">
            <h1 class="give-importer-h1" align="center">
                <?php
                _e( 'Import Subscriptions', 'give' );

                if ( ! empty( $_POST['mapto'] ) && ! empty( $_GET['dry_run'] ) ) {
                    printf(
                        '<strong> %s</strong>',
                        __( '(Dry Run)', 'give' )
                    );
                }
                ?>
            </h1>
            <div class="inside give-tools-setting-page-import give-import-subscriptions">
                <?php do_action( 'give_tools_import_subscriptions_form_before_start' ); ?>
                <form method="post" id="give-import-subscriptions-form" class="give-import-form tools-setting-page-import tools-setting-page-import">
                    <?php do_action( 'give_tools_import_subscriptions_form_start' ); ?>
                    <?php do_action( 'give_tools_import_subscriptions_form_end' ); ?>
                </form>
                <?php do_action( 'give_tools_import_subscriptions_form_after_end' ); ?>
            </div><!-- .inside -->
        </div><!-- .postbox -->
    </div><!-- #poststuff -->
<?php
do_action( 'give_tools_import_subscriptions_main_after' );


