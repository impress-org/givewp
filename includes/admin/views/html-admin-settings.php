<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Bailout: Do not output anything if setting tab is not defined.
if( ! empty( $tabs ) ) :
	/**
	 * Filter the form action.
	 *
	 * Note: filter dynamically fire on basis of setting page slug
	 * For example: if you register a setting page with give-settings menu slug and general current tab
	 *              then filter will be give-settings_form_method_tab_general
	 *
	 * @since 1.8
	 */
	$form_method    = apply_filters( self::$setting_filter_prefix . '_form_method_tab_' . $current_tab, 'post' );

	/**
	 * Filter the main form tab.
	 *
	 * Note: You can stop print main form if you want to.filter dynamically fire on basis of setting page slug
	 * For example: if you register a setting page with give-settings menu slug
	 *              then filter will be give-settings_open_form, give-settings_close_form
	 *              We are using this filter in includes/admin/tools/class-settings-data.php#L52
	 *
	 * @since 1.8
	 */
	$form_open_tag  = apply_filters( self::$setting_filter_prefix . '_open_form', '<form method="' . $form_method  . '" id="give-mainform" action="" enctype="multipart/form-data">' );
	$form_close_tag = apply_filters( self::$setting_filter_prefix . '_close_form', '</form>' );
	?>
	<div class="wrap give-settings-page <?php echo self::$setting_filter_prefix . '-setting-page'; ?>">
		<?php echo $form_open_tag; ?>
			<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
				<?php
				foreach ( $tabs as $name => $label ) {
					echo '<a href="' . admin_url( "edit.php?post_type=give_forms&page=" . self::$setting_filter_prefix . "&tab={$name}" ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
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
		<?php echo $form_close_tag; ?>
	</div>
<?php endif; ?>