<?php

namespace Give\Framework\FieldsAPI\Factory;

use Give\Framework\FieldsAPI\FormField;

class Field {

    const TYPE_TEXT = 'text';
    const TYPE_SELECT = 'select';
    const TYPE_TEXTAREA = 'textarea';

    protected static function make( $type, $name ) {
        return new FormField( $type, $name );
    }

    public static function text( $name ) {
        return self::make( self::TYPE_TEXT, $name );
    }

    public static function select( $name ) {
        return self::make( self::TYPE_SELECT, $name );
    }
    
    public static function textarea( $name ) {
        return self::make( self::TYPE_TEXTAREA, $name );
    }
}