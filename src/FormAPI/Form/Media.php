<?php
namespace Give\FormAPI\Form;

class Media extends Field {
	/**
	 * Field value type.
	 * Note: Allow developer to save attachment ID or attachment url as metadata.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $fieldValueType;

	/**
	 * @inheritDoc
	 */
	public function parse( $array ) {
		parent::parse( $array );

		$this->fieldValueType = isset( $array['fvalue'] ) ? $array['fvalue'] : 'url';
	}

	/**
	 * @inheritDoc
	 */
	public function toArray() {
		return array_merge(
			parent::toArray(),
			[
				'fvalue' => $this->fieldValueType,
			]
		);
	}
}
