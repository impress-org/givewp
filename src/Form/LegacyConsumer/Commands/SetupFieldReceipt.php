<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Receipt\DonationReceipt;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

/**
 * @unreleased
 */
class SetupFieldReceipt {

	/**
	 * @unreleased
	 *
	 * @param string $hook
	 */
	public function __construct( DonationReceipt $receipt ) {
		$this->receipt = $receipt;
	}


	/**
	 * @unreleased
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {

		$formID = give_get_payment_meta( $this->receipt->donationId, '_give_payment_form_id' );

		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_{$hook}", $fieldCollection, $formID );

		$fieldCollection->walk( [ $this, 'apply' ] );
	}

	/**
	 * @unreleased
	 *
	 * @param FormField $field
	 *
	 * @return void
	 */
	public function apply( FormField $field ) {

		if ( ! $field->shouldShowInReceipt() ) {
			return;
		}

		if ( $field->shouldStoreAsDonorMeta() ) {
			$this->addDonorLineItem( $field );
		} else {
			$this->addAdditionalLineItems( $field );
		}
	}

	protected function addDonorLineItem( $field ) {
		$donorID = give_get_payment_meta( $this->receipt->donationId, '_give_payment_donor_id' );
		if ( $value = Give()->donor_meta->get_meta( $donorID, $field->getName(), true ) ) {
			$this->receipt
			->getSections()[ DonationReceipt::DONORSECTIONID ]
			->addLineItem(
				[
					'id'    => $field->getName(),
					'label' => $field->getLabel(),
					'value' => $value,
				]
			);
		}
	}

	protected function addAdditionalLineItems( $field ) {
		if ( $value = give_get_payment_meta( $this->receipt->donationId, $field->getName() ) ) {
			$this->receipt
				->getSections()[ DonationReceipt::ADDITIONALINFORMATIONSECTIONID ]
				->addLineItem(
					[
						'id'    => $field->getName(),
						'label' => $field->getLabel(),
						'value' => $value,
					]
				);
		}
	}
}
