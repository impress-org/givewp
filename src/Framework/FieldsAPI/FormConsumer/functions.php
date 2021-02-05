<?php

/**
 * @param string $type
 * @param string $name
 * 
 * @return FormField
 */
function give_field( $type, $name ) {
    return \Give\Framework\FieldsAPI\Factory\Field::$type( $name );
}