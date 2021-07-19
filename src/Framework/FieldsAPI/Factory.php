<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;

/**
 * @unreleased
 */
class Factory {

	/**
	 * @unreleased
	 * @param string $type
	 * @param string $name
	 * @return Node
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
