<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased
 */
class WpEditor extends Field {
	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'wp-editor';

	/**
	 * @unreleased
	 * @param string $name
	 */
	public function __construct( $name ) {
		parent::__construct( $name );

		$this->validationRules->rule( 'richTextEditorType', 'teeny' ); // support: teeny, rich ( without media and quick tags )
	}

	/**
	 * @unreleased
	 *
	 * @param bool $richTextEditorType
	 *
	 * @return $this
	 */
	public function richTextEditorType( $richTextEditorType ){
		$this->validationRules->rule( 'richTextEditorType', $richTextEditorType );
		return $this;
	}

	/**
	 * @unreleased
	 *
	 * @return $this
	 */
	public function getRichTextEditorType(){
		return $this->validationRules->getRule( 'richTextEditorType' );
	}
}
