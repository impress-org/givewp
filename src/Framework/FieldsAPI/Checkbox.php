<?php

namespace Give\Framework\FieldsAPI;

class Checkbox extends Field {

	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasPlaceholder;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'checkbox';

	/**
	 * Helper for marking the checkbox as checked by default
	 *
	 * @unreleased
	 *
	 * @param bool|callable $isChecked
	 *
	 * @return $this
	 */
	public function checked( $isChecked = true ) {
		$default = is_callable( $isChecked ) ? $isChecked() : $isChecked;
		$this->defaultValue( (bool) $default );

		return $this;
	}

	/**
	 * Returns whether the checkbox is checked by default
	 *
	 * @unreleased
	 *
	 * @return bool
	 */
	public function isChecked() {
		return (bool) $this->defaultValue;
	}
}
