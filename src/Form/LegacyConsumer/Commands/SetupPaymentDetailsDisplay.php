<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

/**
 * @unreleased
 */
class SetupPaymentDetailsDisplay {

	/**
	 * @unreleased
	 *
	 * @param int $donationID
	 */
	public function __construct( $donationID ) {
		$this->donationID = $donationID;
	}


	/**
	 * @unreleased
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {

		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_{$hook}", $fieldCollection, get_the_ID() );

		$fieldCollection->walk( [ $this, 'render' ] );
	}

	/**
	 * @unreleased
	 *
	 * @param FormField $field
	 *
	 * @return void
	 */
	public function render( FormField $field ) {
		if ( $field->shouldStoreAsDonorMeta() ) {
			return;
		}
		?>
		<div class="referral-data postbox" style="padding-bottom: 15px;">
			<h3 class="hndle">
				<?php echo $field->getLabel(); ?>
			</h3>
			<div class="inside">    
				<p>   
					<?php echo give_get_meta( $this->donationID, $field->getName(), true ); ?>
				</p>
			</div>
		</div>
		<?php
	}
}
