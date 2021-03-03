<?php

namespace Give\Framework\FieldsAPI\FormField;

trait FieldHelpText {

	/** @var string */
	protected $helpText;

	/**
	 * @param string $helpText
	 * @return $this
	 */
	public function helpText( $helpText ) {
		$this->helpText = $helpText;
		return $this;
	}

	public function getHelpText() {
		return $this->helpText;
	}
}
