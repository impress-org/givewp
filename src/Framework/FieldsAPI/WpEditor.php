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

	/**
	 * @var string
	 */
	const TYPE = 'wp-editor';

	/**
	 * support: teeny, rich ( without media and quick tags )
	 *
	 * @var string
	 */
	protected $editorType = 'teeny';

	/**
	 * @see wp_editor settings: https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
	 *
	 * @var array
	 */
	protected $editorConfig;

	/**
	 * @unreleased
	 *
	 * @param string $name
	 */
	public function __construct( $name ) {
		parent::__construct( $name );
	}

	/**
	 * @unreleased
	 *
	 * @param bool $editorType
	 *
	 * @return $this
	 */
	public function richTextEditorType( $editorType ) {
		$this->editorType = $editorType;

		return $this;
	}

	/**
	 * @unreleased
	 *
	 * @return string
	 */
	public function getRichTextEditorType() {
		return $this->editorType;
	}

	/**
	 * @unreleased
	 *
	 * @param array $editorConfig
	 *
	 * @return $this
	 */
	public function editorConfig( $editorConfig ) {
		$this->editorConfig = $editorConfig;

		return $this;
	}

	/**
	 * @unreleased
	 *
	 * @return array
	 */
	public function getEditorConfig() {
		return wp_parse_args(
			$this->editorConfig,
			[
				'quicktags'     => false,
				'media_buttons' => false,
				'teeny'         => 'teeny' === $this->getRichTextEditorType(),
				'editor_class'  => ' rich-editor',
			]
		);
	}
}
