<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Group;

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
	 * @param Field $field
	 *
	 * @return void
	 */
	public function process( Field $field ) {
		if ( isset( $_POST[ $field->getName() ] ) ) {
			$value = wp_strip_all_tags( $_POST[ $field->getName() ], true );

			if ( $field->shouldStoreAsDonorMeta() ) {
				$donorID = give_get_payment_meta( $this->donationID, '_give_payment_donor_id' );
				Give()->donor_meta->update_meta( $donorID, $field->getName(), $value );
			} else {
				// Store as Donation Meta - default behavior.
				give_update_payment_meta( $this->donationID, $field->getName(), $value );
			}
		}
	}
}
