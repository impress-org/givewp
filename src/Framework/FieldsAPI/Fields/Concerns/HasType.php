<?php

namespace Give\Framework\FieldsAPI\Fields\Concerns;

trait HasType {

	/**
	 * Get the field’s type.
	 *
	 * @return string
	 */
	public function getType() {
		return self::TYPE;
	}
}
