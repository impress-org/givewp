<?php

namespace Give\Form\LegacyConsumer;

use Give\Framework\FieldsAPI\Types;
use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * @since 2.10.2
 * @unreleased Add field classes hook for setting custom class names on the wrapper.
 */
class FieldView {
	const INPUT_TYPE_ATTRIBUTES = [
		Types::PHONE    => 'tel',
		Types::EMAIL    => 'email',
		Types::URL      => 'url',
	];

	/**
	 * @since 2.10.2
	 * @unreleased add $formId as a param
	 * @unreleased Add filter to allow rendering logic for custom fields
	 *
	 * @param Node $field
	 * @param int $formId
	 *
	 * @return void
	 */
	public static function render( Node $field, $formId ) {
		$type = $field->getType();
		$fieldIdAttribute = give( UniqueIdAttributeGenerator::class )->getId( $formId, $field->getName() );

		if ( $type === Types::HIDDEN ) {
			include static::getTemplatePath( 'hidden' );

			return;
		}

		$classList = apply_filters( "give_form_{$formId}_field_classes_{$field->getName()}", [ 'form-row', 'form-row-wide' ] );
		$className = implode( ' ', array_unique( $classList ) );

		echo "<div class=\"{$className}\" data-field-type=\"{$field->getType()}\" data-field-name=\"{$field->getName()}\">";
		// By default, new fields will use templates/label.html.php and templates/base.html.php
		switch ( $type ) {
			case Types::HTML:
			case Types::CHECKBOX:
			case Types::RADIO: // Radio provides its own label
				include static::getTemplatePath( $type );
				break;
			// These fields need a label and have their own template.
			case Types::FILE:
			case Types::SELECT:
			case Types::TEXTAREA:
				include static::getTemplatePath( 'label' );
				include static::getTemplatePath( $type );
				break;
			// By default, include a template and use the base input template.
			case Types::DATE:
			case Types::EMAIL:
			case Types::PHONE:
			case Types::TEXT:
			case Types::URL:
				// Used in the template
				$typeAttribute = array_key_exists( $type, static::INPUT_TYPE_ATTRIBUTES ) ? static::INPUT_TYPE_ATTRIBUTES[ $type ] : 'text';
				include static::getTemplatePath( 'label' );
				include static::getTemplatePath( 'base' );
				break;
			default:
				/**
				 * Provide a custom function to render for a custom node type.
				 *
				 * @unreleased
				 *
				 * @param Node $field The node to render.
				 * @param int $formId The form ID that the node is a part of.
				 *
				 * @void
				 */
				do_action( "give_fields_api_render_{$field->getType()}", $field, $formId );
		}
		echo '</div>';
	}

	/**
	 * @since 2.12.0
	 *
	 * @param  string  $templateName
	 *
	 * @return string
	 */
	protected static function getTemplatePath( $templateName ) {
		return plugin_dir_path( __FILE__ ) . "/templates/{$templateName}.html.php";
	}
}
