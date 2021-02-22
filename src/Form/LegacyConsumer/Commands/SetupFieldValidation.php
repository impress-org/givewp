<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FieldCollection;

/**
 * Setup field validation for custom fields on the required fields hook.
 *
 * @unreleased
 */
class SetupFieldValidation implements HookCommandInterface {
	public function __invoke( $hook ) {

			// Register custom fields during processing to validate required.
			add_action(
				'give_donation_form_required_fields',
				function( $requiredFields, $formID ) use ( $hook ) {
						$fieldCollection = new FieldCollection( 'root' );
						do_action( "give_fields_$hook", $fieldCollection, $formID );
						$fieldCollection->walk(
							function( $field ) use ( &$requiredFields ) {
								if ( $field->isRequired() ) {
										$requiredFields[ $field->getName() ] = $field->getRequiredError();
								}
							}
						);
						return $requiredFields;
				},
				10,
				2
			);
	}
}

