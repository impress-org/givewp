<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use WP_REST_Request;
use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;

/**
 * @unreleased
 */
class AvatarRoute extends RouteAbstract {

	/**
	 * @inheritdoc
	 */
	public function endpoint() {
		return 'avatar';
	}

	/**
	 * @inheritdoc
	 */
	public function args() {
		return [];
	}

	/**
	 * Handles profile update, and returns updated avatar attachment id
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return WP_REST_Request
	 *
	 * @unreleased
	 */
	public function handleRequest( $request ) {
		if ( ! ( is_array( $_POST ) && is_array( $_FILES ) ) ) {
			return;
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$ids = [];

		foreach ( $_FILES as $file ) {
			$upload = wp_handle_upload(
				$file,
				[
					'test_form' => false,
				]
			);

			if ( isset( $upload['url'] ) ) {
				$path = $upload['url'];

				// Check the type of file. We'll use this as the 'post_mime_type'.
				$filetype = wp_check_filetype( basename( $path ), null );

				// Prepare an array of post data for the attachment.
				$attachment = [
					'guid'           => $path,
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $path ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				];

				// Insert the attachment.
				$attachmentId = wp_insert_attachment( $attachment, $path );

				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				// Generate the metadata for the attachment, and update the database record.
				$attachmentData = wp_generate_attachment_metadata( $attachmentId, $path );
				wp_update_attachment_metadata( $attach_id, $attachmentData );

				$ids[] = $attachmentId;
			}
		}

		return [
			'id' => $ids[0],
		];
	}
}
