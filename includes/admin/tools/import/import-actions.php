<?php
/**
 * Exports Give's core settings.
 *
 * Give_Core_Settings class.
 *
 * @since 1.8.16
 * @return void
 */
function give_core_settings_import() {

	$json_string       = file_get_contents( $_FILES['json_file']['tmp_name'] );
	$json_to_array     = json_decode( $json_string, true );
	$host_give_options = get_option( 'give_settings' );

	// Handle pages under General > General.
	$json_to_array['success_page'] = ! empty( $host_give_options['success_page'] ) ? $host_give_options['success_page'] : '';
	$json_to_array['failure_page'] = ! empty( $host_give_options['failure_page'] ) ? $host_give_options['failure_page'] : '';
	$json_to_array['history_page'] = ! empty( $host_give_options['history_page'] ) ? $host_give_options['history_page'] : '';

	// Featured image sizes import under Display Options > Post Types > Featured Image Size.
	if ( 'enabled' === $json_to_array['form_featured_img'] ) {
		$images_sizes = get_intermediate_image_sizes();

		if ( ! in_array( $json_to_array['featured_image_size'], $images_sizes ) ) {
			$json_to_array['featured_image_size'] = $host_give_options['featured_image_size'];
		}
	}

	// Emails > Email Settings > Logo.
	if ( array_key_exists( 'email_logo', $json_to_array ) && ! empty( $json_to_array['email_logo'] ) ) {

		// Need to require these files.
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		$url = $json_to_array['email_logo'];
		$tmp = download_url( $url );

		$new_url = media_sideload_image( $url, 0, null, 'src' );

		if ( ! is_wp_error( $new_url ) ) {
			$json_to_array['email_logo'] = $new_url;
		} else {
			$json_to_array['email_logo'] = $host_give_options['email_logo'];
		}
	}

	update_option( 'give_settings', $json_to_array );
}

add_action( 'give_core_settings_import', 'give_core_settings_import' );
