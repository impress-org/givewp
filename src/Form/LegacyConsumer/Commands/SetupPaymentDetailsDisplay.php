<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

class SetupPaymentDetailsDisplay implements HookCommandInterface {
	public function __invoke( $hook ) {
		add_action(
			'give_view_donation_details_billing_after',
			function( $donationID ) use ( $hook ) {

				$fieldCollection = new FieldCollection( 'root' );
				do_action( "give_fields_$hook", $fieldCollection, get_the_ID() );

				$fieldCollection->walk(
					function( $field ) use ( $donationID ) {
						?>
					<div class="referral-data postbox" style="padding-bottom: 15px;">
						<h3 class="hndle">
								<?php echo $field->getLabel(); ?>
						</h3>
						<div class="inside">    
							<p>   
									<?php echo give_get_meta( $donationID, $field->getName(), true ); ?>
							</p>
						</div>
					</div>
						<?php
					}
				);
			}
		);
	}
}

