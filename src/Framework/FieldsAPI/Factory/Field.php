<?php

namespace Give\Framework\FieldsAPI\Factory;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FormField\FieldTypes;

class Field {

    protected static function make( $type, $name ) {
        return new FormField( $type, $name );
    }

    public static function text( $name ) {
        return self::make( FieldTypes::TYPE_TEXT, $name );
    }

    public static function select( $name ) {
        return self::make( FieldTypes::TYPE_SELECT, $name );
    }
    
    public static function textarea( $name ) {
        return self::make( self::TYPE_TEXTAREA, $name );
    }
}