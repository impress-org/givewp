<?php

use Give\Framework\FieldsAPI\Facades\Factory;

/**
 * @param string $type
 * @param string $name
 *
 * @return FormField
 */
function give_field( $type, $name ) {
	return Factory::make( $type, $name );
}
