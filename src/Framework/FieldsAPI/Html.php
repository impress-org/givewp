<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased
 */
class Html extends Element {

	const TYPE = 'html';

	/** @var string */
	protected $html = '';

	/**
	 * Set the HTML for the element.
	 *
	 * @unreleased
	 *
	 * @param string $html
	 * @return $this
	 */
	public function html( $html ) {
		$this->html = $html;

		return $this;
	}

	/**
	 * Get the HTML for the element.
	 *
	 * @unreleased
	 *
	 * @return string
	 */
	public function getHtml() {
		return $this->html;
	}
}
