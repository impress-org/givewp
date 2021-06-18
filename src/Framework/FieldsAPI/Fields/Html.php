<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\Fields\Contracts\Field;

/**
 * An HTML field.
 *
 * @unreleased
 */
class Html implements Field {

	use Concerns\HasType;
	use Concerns\MakeFieldWithName;
	use Concerns\SerializeAsJson;

	const TYPE = 'html';

	/** @var string */
	protected $html;

	/**
	 * Set the HTML string.
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	public function html( $html ) {
		$this->html = $html;
		return $this;
	}

	/**
	 * Access the HTML string.
	 *
	 * @return string
	 */
	public function getHtml() {
		return $this->html;
	}
}
