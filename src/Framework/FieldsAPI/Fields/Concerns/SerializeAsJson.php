<?php

namespace Give\Framework\FieldsAPI\Fields\Concerns;

use JsonSerializable;

trait SerializeAsJson {

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return array_merge(
			// These values must be serialized for all types
			[
				'type' => self::TYPE,
				'name' => $this->getName(),
			],
			// We (recursively) serialize all of the class’ properties and exclude the list provided.
			array_map(
				static function ( $value ) {
					if ( $value instanceof JsonSerializable ) {
						return $value->jsonSerialize();
					}
					return $value;
				},
				// Only serialize properties which are not hidden.
				array_diff_key(
					get_object_vars( $this ),
					// Respect hidden, if set.
					array_flip( property_exists( $this, 'hidden' ) ? $this->hidden : [] ),
					// Ignore hidden property.
					[ 'hidden' => 'hidden' ]
				)
			)
		);
	}
}
