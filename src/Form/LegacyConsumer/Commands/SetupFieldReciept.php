<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Receipt\DonationReceipt;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

/**
 * @unreleased
 */
class SetupFieldReciept {

	/**
	 * @unreleased
	 *
	 * @param string $hook
	 */
	public function __construct( $hook ) {
		$this->hook = $hook;
	}


	/**
	 * @unreleased
	 *
	 * @return void
	 */
	public function __invoke() {
		add_action(
			'give_new_receipt',
			[ $this, 'process' ]
		);
	}

	public function process( DonationReceipt $receipt ) {

		$this->donationId                   = $receipt->donationId;
		$this->donorSection                 = $receipt->getSections()[ DonationReceipt::DONORSECTIONID ];
		$this->additionalInformationSection = $receipt->getSections()[ DonationReceipt::ADDITIONALINFORMATIONSECTIONID ];

		$formID = give_get_payment_meta( $this->donationId, '_give_payment_form_id' );

		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_{$this->hook}", $fieldCollection, $formID );

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
			$donorID = give_get_payment_meta( $this->donationId, '_give_payment_donor_id' );
			$this->donorSection->addLineItem(
				[
					'id'    => $field->getName(),
					'label' => $field->getLabel(),
					'value' => Give()->donor_meta->get_meta( $donorID, $field->getName(), true ),
				]
			);
		} else {
			$this->additionalInformationSection->addLineItem(
				[
					'id'    => $field->getName(),
					'label' => $field->getLabel(),
					'value' => give_get_payment_meta( $this->donationId, $field->getName() ),
				]
			);
		}

	}
}
