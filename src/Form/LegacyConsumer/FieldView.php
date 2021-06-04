<?php

namespace Give\Form\LegacyConsumer;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FormField\FieldTypes;

/**
 * @since 2.10.2
 */
class FieldView {
	const INPUT_TYPE_ATTRIBUTES = [
		FieldTypes::TYPE_PHONE => 'tel',
		FieldTypes::TYPE_EMAIL => 'email',
		FieldTypes::TYPE_CHECKBOX => 'checkbox',
	];

	/**
	 * @since 2.10.2
	 *
	 * @param FormField $field
	 *
	 * @return void
	 */
	public static function render( FormField $field ) {
		echo "<div class='form-row form-row-wide' data-field-type='{$field->getType()}' data-field-name='{$field->getName()}'>";
		ob_start();
		// By default, new fields will use templates/label.html.php and templates/base.html.php
		switch ( $type = $field->getType() ) {
			case FieldTypes::TYPE_HTML: // This is a free form HTML field.
				do_shortcode( $field->getDefaultValue() );
				break;
			// These field types do not need a label and have their own template.
			case FieldTypes::TYPE_HIDDEN: // Hidden does not need a label for obvious reasons.
			case FieldTypes::TYPE_RADIO: // Radio provides its own label
				include static::getTemplatePath( $type );
				break;
			// These fields need a label and have their own template.
			case FieldTypes::TYPE_SELECT:
			case FieldTypes::TYPE_TEXTAREA:
				include static::getTemplatePath( 'label' );
				include static::getTemplatePath( $type );
				break;
			// By default, include a template and use the base input template.
			default:
				$typeAttribute = static::INPUT_TYPE_ATTRIBUTES[ $type ]; // Override the type attribute
				include static::getTemplatePath( 'label' );
				include static::getTemplatePath( 'base' );
				break;
		}
		echo self::mergeAttributes( ob_get_clean(), $field );
		echo '</div>';
	}

	/**
	 * @since 2.10.2
	 *
	 * @param string $html
	 * @param FormField $field
	 *
	 * @return string
	 */
	protected static function mergeAttributes( $html, FormField $field ) {
		$attributes = array_map(
			function( $key, $value ) {
				return sprintf( '%s="%s"', $key, esc_attr( $value ) );
			},
			array_keys( $field->getAttributes() ),
			array_values( $field->getAttributes() )
		);
		return str_replace( '@attributes', implode( ' ', $attributes ), $html );
	}

	/**
	 * @unreleased
	 *
	 * @param string $templateName
	 *
	 * @return string
	 */
	protected static function getTemplatePath( string $templateName ) {
		return plugin_dir_path( __FILE__ ) . "/templates/{$templateName}.html.php";
	}
}
