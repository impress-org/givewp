<?php

namespace Give\Framework\FieldsAPI\Factory;

use ReflectionClass;
use Give\Framework\FieldsAPI\Types as FieldTypes;

class Field {

	public static function __callStatic( $type, $parameters ) {
		$reflectionClass = new ReflectionClass( FieldTypes::class );
		$types           = array_flip( $reflectionClass->getConstants() );
		if ( ! isset( $types[ $type ] ) ) {
			throw new Exception\TypeNotSupported( $type );
		}
		return self::make( $type, array_shift( $parameters ) );
	}

	protected static function make( $type, $name ) {
		$class = 'Give\\Framework\\FieldsAPI\\' . ucfirst( $type );
		if ( ! class_exists( $class ) ) {
			throw new Exception\TypeNotSupported( $type );
		}
		return new $class( $name );
	}
}
