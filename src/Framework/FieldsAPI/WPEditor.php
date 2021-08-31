<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased
 */
class WPEditor extends Field {
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
	 * WP Editor default config.
	 * @var array
	 */
	private $defaultEditorConfig = [
		'quicktags'     => false,
		'media_buttons' => false,
		'teeny'         => true,
		'editor_class'  => ' give-wp-editor-field',
	];

	/**
	 * @see wp_editor settings: https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
	 *
	 * @var array
	 */
	protected $editorConfig;

	/**
	 * @unreleased
	 *
	 * @return self
	 */
	public function useRichTextEditor(){
		$this->defaultEditorConfig['teeny'] = false;

		return $this;
	}

	/**
	 * @unreleased
	 *
	 * @return self
	 */
	public function useSmallRichTextEditor(){
		$this->defaultEditorConfig['teeny'] = true;

		return $this;
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
			$this->defaultEditorConfig
		);
	}
}
