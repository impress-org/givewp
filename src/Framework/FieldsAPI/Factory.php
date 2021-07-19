<?php

namespace Give\Framework\FieldsAPI;

use ReflectionClass;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\FieldsAPI\Types as FieldTypes;

/**
 * @unreleased
 */
class Factory {

	/**
	 * @unreleased
	 * @param string $type
	 * @param string $name
	 * @return mixed
	 * @throws TypeNotSupported
	 */
	public function make( $type, $name ) {
		$class = 'Give\\Framework\\FieldsAPI\\' . ucfirst( $type );
		if ( ! class_exists( $class ) ) {
			throw new TypeNotSupported( $type );
		}
		return new $class( $name );
	}
}
