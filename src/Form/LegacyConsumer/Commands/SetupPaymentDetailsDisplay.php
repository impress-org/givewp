<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Group;

/**
 * @since 2.10.2
 */
class SetupPaymentDetailsDisplay {

	/**
	 * @since 2.10.2
	 *
	 * @param int $donationID
	 */
	public function __construct( $donationID ) {
		$this->donationID = $donationID;
	}


	/**
	 * @since 2.10.2
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {

		$collection = Group::make( $hook );
		do_action( "give_fields_{$hook}", $collection, get_the_ID() );

		$collection->walk( [ $this, 'render' ] );
	}

	/**
	 * @since 2.10.2
	 *
	 * @param Field $field
	 *
	 * @return void
	 */
	public function render( Field $field ) {
		if ( $field->shouldStoreAsDonorMeta() ) {
			return;
		}
		?>
		<div class="referral-data postbox" style="padding-bottom: 15px;">
			<h3 class="handle">
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
