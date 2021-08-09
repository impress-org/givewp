<?php


namespace Give\Framework\FieldsAPI\Concerns;


trait AllowMultiple {

	/** @var bool */
	protected $allowMultiple = false;

	/**
	 * Set whether the field allows multiple or not.
	 *
	 * @param bool $allowMultiple
	 *
	 * @return $this
	 */
	public function allowMultiple( $allowMultiple = true ) {
		$this->allowMultiple = $allowMultiple;
		return $this;
	}

	/**
	 * Access whether or not the field allows multiple.
	 *
	 * @return bool
	 */
	public function getAllowMultiple() {
		return $this->allowMultiple;
	}
}
