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
	protected $storeAsDonorMeta = false;

	/**
	 * @unreleased
	 * @return $this
	 */
	public function storeAsDonorMeta( $storeAsDonorMeta = true ) {
		$this->storeAsDonorMeta = (bool) $storeAsDonorMeta;
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
