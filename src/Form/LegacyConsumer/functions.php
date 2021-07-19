<?php

use Give\Framework\FieldsAPI\Factory\Field;

/**
 * @param string $type
 * @param string $name
 *
 * @return FormField
 */
function give_field( $type, $name ) {
	return Field::$type( $name );
}
