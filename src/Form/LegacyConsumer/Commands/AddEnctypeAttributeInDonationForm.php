<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;

/**
 * This class set "enctype" form tag  attribute to "multipart/form-data".
 *
 * @NOTE Attribute value will only set if "file" tile custom field register to donation form.
 * This helps to access donor files on server.
 *
 * @unreleased
 */
class AddEnctypeAttributeInDonationForm {
	/**
	 * @var int
	 */
	private $formId;

	/**
	 * @unreleased
	 * @param int $formId
	 */
	public function __construct( $formId ) {
		$this->formId = $formId;
	}

	/**
	 * @unreleased
	 *
	 * @param array $formHtmlAttributes
	 * @param string $hook
	 */
	public function __invoke( $formHtmlAttributes, $hook ){
		$collection = Group::make( $hook );
		do_action( "give_fields_$hook", $collection, $this->formId );
		$collection->walkFields(
			/* @var File $field */
			function( $field ) use ( &$formHtmlAttributes ) {
				if( 'file' === $field->getType() ) {
					$formHtmlAttributes['enctype'] = 'multipart/form-data';
					return $formHtmlAttributes;
				}
			}
		);

		return $formHtmlAttributes;
	}
}
