<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Text;

/**
 * Persist custom field values as donation meta.
 *
 * @since 2.10.2
 */
class SetupFieldPersistance implements HookCommandInterface {

	/**
	 * @since 2.10.2
	 *
	 * @param int $donationID
	 * @param array $donationData
	 */
	public function __construct( $donationID, $donationData ) {
		$this->donationID   = $donationID;
		$this->donationData = $donationData;
	}

	/**
	 * @since 2.10.2
	 *
	 * @param string $hook
	 */
	public function __invoke( $hook ) {
		$collection = Group::make( $hook );
		do_action( "give_fields_$hook", $collection, $this->donationData['give_form_id'] );
		$collection->walkFields( [ $this, 'process' ] );
	}

	/**
	 * @since 2.10.2
	 *
	 * @param Field|Text $field
	 *
	 * @return void
	 */
	public function process( Field $field ) {
		if ( isset( $_POST[ $field->getName() ] ) ) {
			$data = give_clean( $_POST[ $field->getName() ] );
			$value = is_array( $data ) ?
				implode( '|', array_values( array_filter( $data ) ) ):
				$data;

			if ( $field->shouldStoreAsDonorMeta() ) {
				$donorID = give_get_payment_meta( $this->donationID, '_give_payment_donor_id' );
				Give()->donor_meta->update_meta( $donorID, $field->getName(), $value );
			} else {
				// Store as Donation Meta - default behavior.
				give_update_payment_meta( $this->donationID, $field->getName(), $value );
			}
		}
	}

	/**
	 * @unreleased
	 *
	 * @param File $field
	 *
	 * @return array
	 */
	private function saveFiles( $field ){
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$fileIds = [];
		foreach ( $_FILES as $file ) {
			$upload = wp_handle_upload( $file, [ 'test_form' => false, ] );

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
				wp_update_attachment_metadata( $attachmentId, $attachmentData );

				$fileIds[] = $attachmentId;
			}
		}

		return $fileIds;
	}
}
