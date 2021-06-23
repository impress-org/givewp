<?php

namespace Give\Framework\FieldsAPI\Fields\Concerns;

/**
 * @unreleased
 */
trait HasHtmlRootElementClassName {

	/** @var string */
	protected $htmlRootElementClassName;

	/**
	 * Set the root HTML element’s class name.
	 *
	 * @param string $className
	 * @return $this
	 */
	public function htmlRootElementClassName( $className ) {
		$this->htmlRootElementClassName = $className;

		return $this;
	}

	/**
	 * Get the root HTML element’s class name.
	 *
	 * @return string
	 */
	public function getHtmlRootElementClassName() {
		return $this->htmlRootElementClassName;
	}
}
