<?php

namespace Give\Framework\FieldsAPI\Fields\Concerns;

trait HasEmailTag {

	/** @var string */
	protected $emailTag;

	/**
	 * @param string $emailTag
	 * @return $this
	 */
	public function emailTag( $emailTag ) {
		$this->emailTag = $emailTag;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmailTag() {
		return $this->emailTag;
	}
}
