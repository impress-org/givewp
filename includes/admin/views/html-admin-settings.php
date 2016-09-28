<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Bailout: Do not output anything if setting tab is not defined.
if( ! empty( $tabs ) ) :
?>
<div class="wrap give-settings-page">
	<form method="<?php echo esc_attr( apply_filters( self::$setting_filter_prefix . '_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
			}
			do_action( 'give_settings_tabs' );
			?>
		</h2>
		<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
		<?php
		do_action( self::$setting_filter_prefix . "_sections_{$current_tab}_page" );

		self::show_messages();

		do_action( self::$setting_filter_prefix . "_settings_{$current_tab}_page" );

		if ( empty( $GLOBALS['give_hide_save_button'] ) ) : ?>
			<div class="give-submit-wrap">
				<input name="save" class="button-primary give-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'give' ); ?>" />
				<?php wp_nonce_field( 'give-settings' ); ?>
			</div>
		<?php endif; ?>
	</form>
</div>
<?php endif; ?>