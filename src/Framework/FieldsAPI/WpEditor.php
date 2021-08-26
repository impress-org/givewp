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
	 *
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
	public function richTextEditorType( $richTextEditorType ) {
		$this->validationRules->rule( 'richTextEditorType', $richTextEditorType );

		return $this;
	}

	/**
	 * @unreleased
	 *
	 * @return string
	 */
	public function getRichTextEditorType() {
		return $this->validationRules->getRule( 'richTextEditorType' );
	}

	/**
	 * @unreleased
	 *
	 * @param array $editorConfig
	 *
	 * @return $this
	 */
	public function editorConfig( $editorConfig ) {
		$this->validationRules->rule( 'editorConfig', $editorConfig );

		return $this;
	}

	/**
	 * @unreleased
	 *
	 * @return array
	 */
	public function getEditorConfig() {
		return wp_parse_args(
			$this->validationRules->getRule( 'editorConfig' ),

			// @see wp_editor settings: https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
			[
				'quicktags'     => false,
				'media_buttons' => false,
				'teeny'         => 'teeny' === $this->getRichTextEditorType(),
				'editor_class'  => ' rich-editor',
			]
		);
	}
}
