<?php

namespace Give\Form\LegacyConsumer;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FormField\FieldTypes;
use Give\Helpers\Html;

/**
 * @since 2.10.2
 */
class FieldView {
	const INPUT_TYPE_ATTRIBUTES = [
		FieldTypes::TYPE_PHONE    => 'tel',
		FieldTypes::TYPE_EMAIL    => 'email',
		FieldTypes::TYPE_CHECKBOX => 'checkbox',
		FieldTypes::TYPE_URL      => 'url',
	];

	/**
	 * @since 2.10.2
	 *
	 * @param FormField $field
	 *
	 * @return void
	 */
	public static function render( FormField $field ) {
		$type = $field->getType();

		if ( $type === FieldTypes::TYPE_HIDDEN ) {
			include static::getTemplatePath( 'hidden' );

			return;
		}

		// Set the class for the input element (used in the templates)
		$classAttribute = Html::classNames(
			'give-input',
			[
				'required' => $field->isRequired(),
			]
		);

		echo "<div class=\"form-row form-row-wide\" data-field-type=\"{$field->getType()}\" data-field-name=\"{$field->getName()}\">";
		// By default, new fields will use templates/label.html.php and templates/base.html.php
		switch ( $type ) {
			case FieldTypes::TYPE_HTML: // This is a free form HTML field.
				echo do_shortcode( $field->getDefaultValue() );
				break;
			// These field types do not need a label and have their own template.
			case FieldTypes::TYPE_RADIO: // Radio provides its own label
				include static::getTemplatePath( $type );
				break;
			// These fields need a label and have their own template.
			case FieldTypes::TYPE_SELECT:
				$selectedOptions = is_array( $selectedOptions = $field->getSelected() ) ? $selectedOptions : [ $selectedOptions ];
				include static::getTemplatePath( 'label' );
				include static::getTemplatePath( 'select' );
				break;
			case FieldTypes::TYPE_TEXTAREA:
				include static::getTemplatePath( 'label' );
				include static::getTemplatePath( 'textarea' );
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
