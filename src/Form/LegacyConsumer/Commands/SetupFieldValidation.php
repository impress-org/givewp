<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Group;

/**
 * Setup field validation for custom fields on the required fields hook.
 *
 * @NOTE This is reducing on required fields, so it doesn't implement the shared interface. This is a special case.
 *
 * @since 2.10.2
 */
class SetupFieldValidation {

	/**
	 * @since 2.10.2
	 *
	 * @param int $formID
	 */
	public function __construct( $formID ) {
		$this->formID = $formID;
	}

	/**
	 * @since 2.10.2
	 *
	 * @param Field[] $requiredFields
	 * @param string $hook
	 *
	 * @return array
	 */
	public function __invoke( $requiredFields, $hook ) {
		$collection = Group::make( $hook );
		do_action( "give_fields_$hook", $collection, $this->formID );
		$collection->walkFields(
			function( $field ) use ( &$requiredFields ) {
				if ( $field->isRequired() ) {
					$requiredFields[ $field->getName() ] = $field->getRequiredError();
				}
			}
		);
		return $requiredFields;
	}
}

