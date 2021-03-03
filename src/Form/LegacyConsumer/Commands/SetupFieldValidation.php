<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FieldCollection;

/**
 * Setup field validation for custom fields on the required fields hook.
 *
 * @unreleased
 */
class SetupFieldValidation implements HookCommandInterface {

	public function __construct( &$requiredFields, $formID ) {
		$this->requiredFields = $requiredFields;
		$this->formID         = $formID;
	}

	public function __invoke( $hook ) {
		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_$hook", $fieldCollection, $this->formID );
		$fieldCollection->walk( [ $this, 'process' ] );
	}

	public function process( $field ) {
		if ( $field->isRequired() ) {
			$this->requiredFields[ $field->getName() ] = $field->getRequiredError();
		}
	}
}

