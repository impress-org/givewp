<?php

namespace Give\Framework\FieldsAPI\FormField;

/**
 * @unreleased
 */
trait FieldStoreAsMeta {

	/**
	 * @unreleased
	 * @var bool
	 */
	public $storeAsDonorMeta = false;

	/**
	 * @unreleased
	 * @return $this
	 */
	public function storeAsDonorMeta() {
		$this->storeAsDonorMeta = true;
		return $this;
	}

	/**
	 * @unreleased
	 * @return bool
	 */
	public function shouldStoreAsDonorMeta() {
		return $this->storeAsDonorMeta;
	}
}
