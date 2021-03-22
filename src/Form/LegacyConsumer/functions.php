<?php

use Give\Framework\FieldsAPI\Factory\Field;
use Give\Framework\FieldsAPI\FormField;

/**
 * @param string $type
 * @param string $name
 *
 * @return FormField
 */
function give_field( $type, $name ) {
	return Field::$type( $name );
}
