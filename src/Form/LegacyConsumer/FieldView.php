<?php

namespace Give\Form\LegacyConsumer;

use Give\Framework\FieldsAPI\Fields\Contracts\Field;
use Give\Framework\FieldsAPI\Fields\Types;
use Give\Framework\FieldsAPI\FieldCollection\Contract\Node;

/**
 * @since 2.10.2
 */
class FieldView {
	const INPUT_TYPE_ATTRIBUTES = [
		Types::PHONE    => 'tel',
		Types::EMAIL    => 'email',
		Types::CHECKBOX => 'checkbox',
		Types::URL      => 'url',
	];

	/**
	 * @since 2.10.2
	 *
	 * @param Node|Field $field
	 *
	 * @return void
	 */
	public static function render( Node $field ) {
		$type = $field->getType();

		//echo "<pre>";
		//echo esc_attr( json_encode($field, JSON_PRETTY_PRINT) );
		//echo "</pre>";

		if ( $type === Types::HIDDEN ) {
			include static::getTemplatePath( 'hidden' );

			return;
		}

		// Set the class for the input element (used in the templates)

		echo "<div class=\"form-row form-row-wide\" data-field-type=\"{$field->getType()}\" data-field-name=\"{$field->getName()}\">";
		// By default, new fields will use templates/label.html.php and templates/base.html.php
		switch ( $type ) {
			case Types::HTML: // This is a free form HTML field.
				echo do_shortcode( $field->getHtml() );
				break;
			// These field types do not need a label and have their own template.
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
	 * @unreleased
	 *
	 * @param  string  $templateName
	 *
	 * @return string
	 */
	protected static function getTemplatePath( $templateName ) {
		return plugin_dir_path( __FILE__ ) . "/templates/{$templateName}.html.php";
	}
}
