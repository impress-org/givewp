<?php

namespace Give\Framework\FieldsAPI\FormField;

/**
 * @unreleased
 */
trait FieldReceipt {

	/**
	 * @unreleased
	 * @var bool
	 */
	protected $showInReceipt = false;

	/**
	 * @unreleased
	 * @return $this
	 */
	public function showInReceipt( $showInReceipt = true ) {
		$this->showInReceipt = $showInReceipt;
		return $this;
	}

	/**
	 * @unreleased
	 * @return bool
	 */
	public function shouldShowInReceipt() {
		return $this->showInReceipt;
	}
}
