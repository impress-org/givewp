<?php
/**
 * Exports Give's core settings.
 *
 * Give_Core_Settings class.
 *
 * @since 1.5
 * @return void
 */
function give_core_settings_import() {

	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	$json_string   = file_get_contents( $_FILES['json_file']['tmp_name'] );
	$json_to_array = json_decode( $json_string, true );

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
		$tmp = download_url( $url );

		if ( is_wp_error( $tmp ) ) {

		}

		$file_array = array();

		// Set variables for storage and fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches );
		$file_array['name']     = basename( $matches[0] );
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink.
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}

		// Do the validation and storage operation.
		$id = media_handle_sideload( $file_array, 0 );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		}

		$src = wp_get_attachment_url( $id );
		$json_to_array['email_logo'] = $src;
	}

	update_option( 'give_settings', $json_to_array );
}

add_action( 'give_core_settings_import', 'give_core_settings_import' );

function q($data) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';
	die;
}