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
			echo ob_get_clean();
		echo '</div>';
	}
}
