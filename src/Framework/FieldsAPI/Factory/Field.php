<?php

namespace Give\Framework\FieldsAPI\Factory;

use ReflectionClass;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FormField\FieldTypes;
use Give\Framework\FieldsAPI\Factory\Exception\TypeNotSupported;

class Field {

	public static function __callStatic( $type, $parameters ) {
		$reflectionClass = new ReflectionClass( FieldTypes::class );
		$types           = array_flip( $reflectionClass->getConstants() );
		if ( ! isset( $types[ $type ] ) ) {
			throw new TypeNotSupported( $type );
		}
		return self::make( $type, array_shift( $parameters ) );
	}

	protected static function make( $type, $name ) {
		return new FormField( $type, $name );
	}
}
