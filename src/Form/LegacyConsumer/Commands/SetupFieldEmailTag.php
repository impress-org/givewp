<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

/**
 * @unreleased
 */
class SetupFieldEmailTag {

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
			'give_add_email_tags',
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

		$fieldCollection->walk( [ $this, 'register' ] );
	}

	/**
	 * @unreleased
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
					$value = give_get_meta( $args['payment_id'], $field->getName(), true );
					return ( ! empty( $value ) ) ? wp_kses_post( $value ) : '';
				},
			]
		);
	}
}
