<?php

use Give\Framework\FieldsAPI\Factory;

/**
 * @param string $type
 * @param string $name
 *
 * @return FormField
 */
function give_field( $type, $name ) {
	return Factory::$type( $name );
}
