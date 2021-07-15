<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasName {

	/** @var string */
	protected $name;

	/**
	 * Get the field’s name.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
}
