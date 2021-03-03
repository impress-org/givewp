<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;

/**
 * @unreleased
 */
class SetupFieldConfirmation {

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
			'give_payment_receipt_after',
			[ $this, 'process' ],
			10,
			2
		);
	}

	/**
	 * @unreleased
	 *
	 * @param $payment
	 * @param array $receipt_args
	 *
	 * @return void
	 */
	public function process( $payment, $receipt_args ) {

		$this->payment     = $payment;
		$this->receiptArgs = $receipt_args;
		$this->donationID  = $payment->ID;

		$formID = give_get_payment_meta( $this->donationId, '_give_payment_form_id' );

		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_{$this->hook}", $fieldCollection, $formID );

		$fieldCollection->walk( [ $this, 'render' ] );
	}

	public function render( FormField $field ) {

		if ( ! $field->shouldShowInReceipt() ) {
			return;
		}

		?>
			<tr>
				<td scope="row">
					<strong>
						<?php echo $field->getLabel(); ?>
					</strong>
				</td>
				<td>
				<?php if ( $field->shouldStoreAsDonorMeta() ) : ?>
					<?php
						$donorID = give_get_payment_meta( $this->donationID, '_give_payment_donor_id' );
						echo Give()->donor_meta->get_meta( $donorID, $field->getName(), true );
					?>
				<?php else : ?>
					<?php echo give_get_payment_meta( $this->donationID, $field->getName() ); ?>
				<?php endif; ?>
				</td>
			</tr>
		<?php
	}
}
