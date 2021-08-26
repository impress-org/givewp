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
	 * @unreleased add $formID as a param
	 *
	 * @param Node $field
	 * @param int $formID
	 *
	 * @return void
	 */
	public static function render( Node $field, $formID ) {
		$type = $field->getType();

		if ( $type === Types::HIDDEN ) {
			include static::getTemplatePath( 'hidden' );

			return;
		}

		$classList = apply_filters( "give_form_{$formID}_field_classes_{$field->getName()}", [ 'form-row', 'form-row-wide' ] );
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
			case  Types::WP_EDITOR:
				include static::getTemplatePath( 'label' );
				include static::getTemplatePath( $type );
				break;
			// By default, include a template and use the base input template.
			default:
				// Used in the template
				$typeAttribute = array_key_exists( $type, static::INPUT_TYPE_ATTRIBUTES ) ? static::INPUT_TYPE_ATTRIBUTES[ $type ] : 'text';
				include static::getTemplatePath( 'label' );
				include static::getTemplatePath( 'base' );
				break;
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
