<?php
/**
 * Exports Give's core settings.
 *
 * Give_Core_Settings class.
 *
 * @since 1.8.15
 * @return void
 */
function give_core_settings_import() {

	$json_string   = file_get_contents( $_FILES['json_file']['tmp_name'] );
	$json_to_array = json_decode( $json_string, true );

	// Unset General Pages.
	unset( $json_to_array['success_page'], $json_to_array['failure_page'], $json_to_array['history_page'] );

	// Featured image sizes import.
	if ( 'enabled' === $json_to_array['form_featured_img'] ) {
		$images_sizes = get_intermediate_image_sizes();

		if ( ! in_array( $json_to_array['featured_image_size'], $images_sizes ) ) {
			unset( $json_to_array['featured_image_size'] );
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
		$url = 'https://nikonrumors.com/wp-content/uploads/2014/03/Nikon-1-V3-sample-photo.jpg';
		$tmp = download_url( $url );

		$new_url = media_sideload_image( $url, 0, null, 'src' );

		if ( ! is_wp_error( $new_url ) ) {
			$json_to_array['email_logo'] = $new_url;
		}
	}

	update_option( 'give_settings', $json_to_array );
}

add_action( 'give_core_settings_import', 'give_core_settings_import' );
