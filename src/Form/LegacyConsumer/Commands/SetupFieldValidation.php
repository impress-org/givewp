<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Form\LegacyConsumer\Validators\FileUploadValidator;
use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;
use function do_action;
use function give_set_error;


/**
 * Setup field validation for custom fields on the checkout error check fields hook.
 *
 * @NOTE For custom field like file, we can not process validation on ajax required.
 * This class validate these fields on donation form submission.
 *
 * @package Give\Form\LegacyConsumer\Commands
 * @since 2.10.2
 */
class SetupFieldValidation {
	/**
	 * @var int
	 */
	private $formId;

	/**
	 * @since 2.10.2
	 *
	 * @param int $formId
	 */
	public function __construct( $formId ) {
		$this->formId = $formId;
	}

	/**
	 * @since 2.10.2
	 * @unreleased  Add support for file field validation
	 *
	 * @param string $hook
	 */
	public function __invoke( $hook ) {
		$collection = Group::make( $hook );
		do_action( "give_fields_$hook", $collection, $this->formId );
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

					case 'wp-editor':
						// Are we processing donation form validation on ajax?
						if( isset( $_POST['give_ajax'] ) ) {
							return;
						}

						// wp editor content does not available on ajax donation form validation, so we need to validate it on donation form submission.
						if ( $field->isRequired() && empty( $_POST[ $field->getName() ] ) ) {
							give_set_error( "give-{$field->getName()}-required-field-missing", $field->getRequiredError()['error_message'] );
						}
						break;

					default:
						if ( $field->isRequired() && empty( $_POST[ $field->getName() ] ) ) {
							give_set_error( "give-{$field->getName()}-required-field-missing", $field->getRequiredError()['error_message'] );
						}
				}
			}
		);
	}
}
