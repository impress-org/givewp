<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

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
		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_{$hook}", $fieldCollection, get_the_ID() );

		$fieldCollection->walk( [ $this, 'register' ] );
	}

	/**
	 * @since 2.10.2
	 *
	 * @param FormField $field
	 *
	 * @return void
	 */
	public function register( FormField $field ) {

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
