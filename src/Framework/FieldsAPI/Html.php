<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.2
 */
class Html extends Element {

	const TYPE = 'html';

	/** @var string */
	protected $html = '';

	/**
	 * Set the HTML for the element.
	 *
	 * @since 2.12.2
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
	 * @since 2.12.2
	 *
	 * @return string
	 */
	public function getHtml() {
		return $this->html;
	}
}
