<?php

namespace Give\Form\LegacyConsumer;

use Give\Framework\FieldsAPI\FormField;

/**
 * @unreleased
 */
class FieldView {

	/**
	 * @unreleased
	 *
	 * @param FormField $field
	 *
	 * @return void
	 */
	public static function render( FormField $field ) {
		echo "<div class='form-row form-row-wide' data-field-type='{$field->getType()}' data-field-name='{$field->getName()}'>";
			ob_start();
			include plugin_dir_path( __FILE__ ) . '/templates/label.html.php';
			include plugin_dir_path( __FILE__ ) . '/templates/' . $field->getType() . '.html.php';
			echo self::mergeAttributes( ob_get_clean(), $field );
		echo '</div>';
	}

	/**
	 * @unreleased
	 *
	 * @param string $html
	 * @param FormField $field
	 *
	 * @return string
	 */
	protected static function mergeAttributes( $html, $field ) {
		$attributes = array_map(
			function( $key, $value ) {
				return sprintf( '%s="%s"', $key, esc_attr( $value ) );
			},
			array_keys( $field->getAttributes() ),
			array_values( $field->getAttributes() )
		);
		return str_replace( '@attributes', implode( ' ', $attributes ), $html );
	}
}
