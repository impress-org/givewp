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
	 * @var bool
	 */
	public $storeAsDonationMeta = false;

	/**
	 * @unreleased
	 * @param bool $storeAsDonorMeta
	 * @return $this
	 */
	public function storeAsDonorMeta( $storeAsDonorMeta = true ) {
		$this->storeAsDonorMeta = $storeAsDonorMeta;
		return $this;
	}

	/**
	 * @unreleased
	 * @return bool
	 */
	public function shouldStoreAsDonorMeta() {
		return $this->storeAsDonorMeta;
	}

	/**
	 * @unreleased
	 * @param bool $storeAsDonationMeta
	 * @return $this
	 */
	public function storeAsDonationMeta( $storeAsDonationMeta = true ) {
		$this->storeAsDonationMeta = $storeAsDonationMeta;
		return $this;
	}

	/**
	 * @unreleased
	 * @return bool
	 */
	public function shouldStoreAsDonationMeta() {
		return $this->storeAsDonationMeta;
	}
}
