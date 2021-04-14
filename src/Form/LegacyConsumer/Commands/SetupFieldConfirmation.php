<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;

/**
 * @since 2.10.2
 */
class SetupFieldConfirmation {

	/**
	 * @since 2.10.2
	 *
	 * @param Donation $payment
	 * @param array $receiptArgs
	 */
	public function __construct( $payment, $receiptArgs ) {
		$this->payment     = $payment;
		$this->receiptArgs = $receiptArgs;
	}

	/**
	 * @since 2.10.2
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {

		$formID = give_get_payment_meta( $this->payment->ID, '_give_payment_form_id' );

		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_{$hook}", $fieldCollection, $formID );

		$fieldCollection->walk( [ $this, 'render' ] );
	}

	/**
	 * @since 2.10.2
	 *
	 * @param FormField $field
	 *
	 * @return void
	 */
	public function render( FormField $field ) {

		if ( ! $field->shouldShowInReceipt() ) {
			return;
		}

		if ( $field->shouldStoreAsDonorMeta() ) {
			$donorID = give_get_payment_meta( $this->payment->ID, '_give_payment_donor_id' );
			$value   = Give()->donor_meta->get_meta( $donorID, $field->getName(), true );
		} else {
			$value = give_get_payment_meta( $this->payment->ID, $field->getName() );
		}

		if ( ! $value ) {
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
					<?php echo $value; ?>
				</td>
			</tr>
		<?php
	}
}
