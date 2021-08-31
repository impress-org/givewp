<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Form\LegacyConsumer\Actions\UploadFilesAction;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Text;
use Give\Framework\FieldsAPI\WPEditor;
use function do_action;
use function give_clean;
use function give_get_payment_meta;
use function give_update_payment_meta;

/**
 * Persist custom field values as donation meta.
 *
 * @since 2.10.2
 */
class SetupFieldPersistence implements HookCommandInterface {
	/**
	 * @var int
	 */
	private $donationId;
	/**
	 * @var array
	 */
	private $donationData;

	/**
	 * @param int $donationId
	 * @param array $donationData
	 *
	 * @since 2.10.2
	 *
	 */
	public function __construct( $donationId, $donationData ) {
		$this->donationId   = $donationId;
		$this->donationData = $donationData;
	}

	/**
	 * @param string $hook
	 *
	 * @since 2.10.2
	 *
	 */
	public function __invoke( $hook ) {
		$collection = Group::make( $hook );
		do_action( "give_fields_$hook", $collection, $this->donationData['give_form_id'] );
		$collection->walkFields( [ $this, 'process' ] );
	}

	/**
	 * @param Field|Text|File $field
	 *
	 * @return void
	 * @since 2.10.2
	 *
	 */
	public function process( Field $field ) {
		switch ( $field->getType() ) {
			case 'file':
				if ( isset( $_FILES[ $field->getName() ] ) ) {

					$fileUploader = new UploadFilesAction( $field );
					$fileIds      = $fileUploader();

					foreach ( $fileIds as $fileId ) {
						if ( $field->shouldStoreAsDonorMeta() ) {
							$donorID = give_get_payment_meta( $this->donationId, '_give_payment_donor_id' );
							Give()->donor_meta->add_meta( $donorID, $field->getName(), $fileId );
						} else {
							// Store as Donation Meta - default behavior.
							give()->payment_meta->add_meta( $this->donationId, $field->getName(), $fileId );
						}
					}
				}
				break;

			default:
				if ( isset( $_POST[ $field->getName() ] ) ) {
					$data  = give_clean( $_POST[ $field->getName() ] );
					$value = is_array( $data ) ?
						implode( '| ', array_values( array_filter( $data ) ) ) :
						$data;

					if( $field instanceof WPEditor ) {
						$value = wp_kses_post( $data );
					}

					if ( $field->shouldStoreAsDonorMeta() ) {
						$donorID = give_get_payment_meta( $this->donationId, '_give_payment_donor_id' );
						Give()->donor_meta->update_meta( $donorID, $field->getName(), $value );
					} else {
						// Store as Donation Meta - default behavior.
						give_update_payment_meta( $this->donationId, $field->getName(), $value );
					}
				}
		}
	}
}
