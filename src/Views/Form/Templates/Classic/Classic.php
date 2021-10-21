<?php

namespace Give\Views\Form\Templates\Classic;

use Give\Form\Template;
use Give\Form\Template\Hookable;
use Give\Form\Template\Scriptable;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give_Scripts;
use InvalidArgumentException;

/**
 * Classic Donation Form Template
 *
 * @unreleased
 */
class Classic extends Template implements Hookable, Scriptable {
	/**
	 * @var array
	 */
	private $options;

	public function __construct() {
		$this->options = FormTemplateUtils::getOptions();
	}

	/**
	 * @inheritDoc
	 */
	public function getID() {
		return 'classic';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'Classic Donation Form', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getImage() {
		return GIVE_PLUGIN_URL . 'assets/dist/images/admin/ClassicForm.jpg';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptionsConfig() {
		return require 'optionConfig.php';
	}

	/**
	 * @return array
	 */
	public function getFormOptions() {
		return $this->options;
	}

	/**
	 * @inheritDoc
	 */
	public function loadHooks() {
		//TODO: check what hooks we have to add/remove
	}

	/**
	 * @inheritDoc
	 */
	public function loadScripts() {
		// Font
		// TODO: check font option
		wp_enqueue_style( 'give-google-font-montserrat', 'https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700i&display=swap', [], GIVE_VERSION );

		// If default Give styles are disabled globally, enqueue Give default styles here
		if ( ! give_is_setting_enabled( give_get_option( 'css' ) ) ) {
			wp_enqueue_style( 'give-styles', ( new Give_Scripts )->get_frontend_stylesheet_uri(), [], GIVE_VERSION );
		}

		// Form styles
		wp_enqueue_style( 'give-classic-template-css', GIVE_PLUGIN_URL . 'assets/dist/css/give-classic-template.css', [ 'give-styles' ], GIVE_VERSION );

		// Inline CSS
		// TODO: discuss with Nathan
		wp_add_inline_style(
			'give-classic-template-css',
			$this->loadFile( 'css/inline.css.php', [
				'options' => $this->options
			] )
		);

		// JS
		wp_enqueue_script( 'give-classic-template-js', GIVE_PLUGIN_URL . 'assets/dist/js/give-classic-template.js', [ 'give' ], GIVE_VERSION, true );
		wp_localize_script( 'give-classic-template-js', 'classicTemplateOptions', $this->options );
	}

	/**
	 * @inheritDoc
	 */
	public function getLoadingView() {
		return $this->loadFile( 'views/loading.php', [
			'options' => $this->options
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function renderLoadingView( $formId = null ) {
		echo $this->getLoadingView();
	}

	/**
	 * @inheritDoc
	 */
	public function getReceiptView() {
		return $this->getFilePath( 'views/receipt.php' );
	}

	/**
	 * Load file
	 *
	 * @param  string  $file
	 * @param  array  $args
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 *
	 */
	protected function loadFile( $file, $args = [] ) {
		$filePath = $this->getFilePath( $file );

		if ( ! file_exists( $filePath ) ) {
			throw new InvalidArgumentException( "File {$filePath} does not exist" );
		}

		ob_start();
		extract( $args );
		include $filePath;

		return ob_get_clean();
	}

	/**
	 * Get file path
	 *
	 * @param  string  $file
	 *
	 * @return string
	 */
	protected function getFilePath( $file = '' ) {
		return GIVE_PLUGIN_DIR . "src/Views/Form/Templates/Classic/resources/{$file}";
	}
}
