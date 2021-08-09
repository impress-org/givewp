<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Group;

/**
 * @since 2.10.2
 */
class SetupFieldEmailTag {


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

		$collection->walkFields( [ $this, 'register' ] );
	}

	/**
	 * @since 2.10.2
	 *
	 * @param Field $field
	 *
	 * @return void
	 */
	public function register( Field $field ) {
		give_add_email_tag(
			[
				'tag'      => $field->getEmailTag() ?: $field->getName(), // The tag name.
				'desc'     => $field->getLabel(), // For admins.
				'context'  => 'donation',
				'is_admin' => false, // default is false.
				'func'     => function( $args, $tag ) use ( $field ) {

					if ( $field->shouldStoreAsDonorMeta() ) {
						$donorID = give_get_payment_meta( $args['payment_id'], '_give_payment_donor_id' );
						$value   = Give()->donor_meta->get_meta( $donorID, $field->getName(), true );
					} else {
						$value = give_get_meta( $args['payment_id'], $field->getName(), true );
					}

					return ( ! empty( $value ) ) ? wp_kses_post( $value ) : '';
				},
			]
		);
	}
}
