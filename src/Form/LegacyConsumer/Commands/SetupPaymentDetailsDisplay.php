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
			'give_view_donation_details_billing_after',
			[ $this, 'process' ]
		);
	}

	/**
	 * @unreleased
	 *
	 * @param int $donationID
	 *
	 * @return void
	 */
	public function process( $donationID ) {

		$this->donationID = $donationID;

		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_{$this->hook}", $fieldCollection, get_the_ID() );

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
