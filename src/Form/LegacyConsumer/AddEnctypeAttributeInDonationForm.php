<?php

namespace Give\Form\LegacyConsumer;

use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;
use function do_action;

/**
 * This class set "enctype" form tag  attribute to "multipart/form-data".
 *
 * @NOTE Attribute value will only set if "file" tile custom field register to donation form.
 * This helps to access donor files on server.
 *
 * @since 2.14.0
 */
class AddEnctypeAttributeInDonationForm {
	/**
	 * @var int
	 */
	private $formId;

	/**
	 * @since 2.14.0
	 * @param int $formId
	 */
	public function __construct( $formId ) {
		$this->formId = $formId;
	}

	/**
	 * @since 2.14.0
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
