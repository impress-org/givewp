<?php

namespace Give\Framework\FieldsAPI\Fields;

/**
 * An HTML field.
 *
 * @unreleased
 */
class Html extends Block {

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
