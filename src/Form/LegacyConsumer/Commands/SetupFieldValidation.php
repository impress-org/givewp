<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Form\LegacyConsumer\Validators\FileUploadValidator;
use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;


/**
 * Setup field validation for custom fields on the checkout error check fields hook.
 *
 * @NOTE For custom field like file, we can not process validation on ajax required.
 * This class validate these fields on donation form submission.
 *
 * @package Give\Form\LegacyConsumer\Commands
 * @unreleased
 */
class SetupFieldValidation {
	/**
	 * @var int
	 */
	private $formID;

	/**
	 * @unreleased
	 *
	 * @param int $formID
	 */
	public function __construct( $formID ) {
		$this->formID = $formID;
	}

	/**
	 * @unreleased
	 *
	 * @param string $hook
	 *
	 * @return array
	 */
	public function __invoke( $hook ) {
		$collection = Group::make( $hook );
		do_action( "give_fields_$hook", $collection, $this->formID );
		$collection->walkFields(
			/* @var File $field */
			function( $field ) {
				switch ( $field->getType() ) {
					case 'file':
						// Are we processing donation form validation on ajax?
						if( isset( $_POST['give_ajax'] ) ) {
							return;
						}

						$validator = new FileUploadValidator( $field );
						$validator();
						break;

					default:
						if ( $field->isRequired() && ! isset( $_POST[ $field->getName() ] ) ) {
							give_set_error( "give-{$field->getName()}-required-field-missing", $field->getRequiredError()['error_message'] );
						}
				}
			}
		);
	}
}
