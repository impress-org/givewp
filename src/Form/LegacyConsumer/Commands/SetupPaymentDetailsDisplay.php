<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

class SetupPaymentDetailsDisplay implements HookCommandInterface {

	// Public properties of type ClosureProxy will be available to callbacks.

	public $process;
	public $output;

	public function __construct() {
		$this->process = new Helper\ClosureProxy( $this->process(), $this );
		$this->output  = new Helper\ClosureProxy( $this->output(), $this );
	}

	public function __invoke( $hook ) {
		add_action(
			'give_view_donation_details_billing_after',
			$this->process->with(
				[
					'hook' => $hook,
				]
			)
		);
	}

	public function process() {
		return function( $donationID ) {
			$fieldCollection = new FieldCollection( 'root' );
			do_action( "give_fields_$this->hook", $fieldCollection, get_the_ID() );

			$fieldCollection->walk(
				$this->output->with( [ 'donationID' => $donationID ] )
			);
		};
	}

	public function output() {
		return function( $field ) {
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
		};
	}
}
