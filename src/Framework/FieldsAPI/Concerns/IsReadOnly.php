<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait IsReadOnly {

	/** @var bool */
	protected $readOnly = false;

	/**
	 * @param bool $readOnly
	 * @return $this
	 */
	public function readOnly( $readOnly = true ) {
		$this->readOnly = $readOnly;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isReadOnly() {
		return $this->readOnly;
	}
}
