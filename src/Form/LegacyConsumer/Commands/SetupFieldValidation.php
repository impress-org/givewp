<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FieldCollection;

/**
 * Setup field validation for custom fields on the required fields hook.
 *
 * @unreleased
 */
class SetupFieldValidation {

	public function __construct( $formID ) {
		$this->formID = $formID;
	}

	public function __invoke( $requiredFields, $hook ) {
		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_$hook", $fieldCollection, $this->formID );
		$fieldCollection->walk(
			function( $field ) use ( &$requiredFields ) {
				if ( $field->isRequired() ) {
					$requiredFields[ $field->getName() ] = $field->getRequiredError();
				}
			}
		);
		return $requiredFields;
	}
}

