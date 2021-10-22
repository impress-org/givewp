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

	/**
	 * @var bool
	 */
	private $scriptsLoaded = false;

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
		// Display header
		if ( 'enabled' === $this->options[ 'appearance' ][ 'display_header' ] ) {
			add_action( 'give_pre_form', [ $this, 'renderHeader' ] );
		}

		/**
		 * Remove actions
		 */
		// Remove goal.
		remove_action( 'give_pre_form', 'give_show_goal_progress' );
		// Remove intermediate continue button which appear when display style set to other then onpage.
		remove_action( 'give_after_donation_levels', 'give_display_checkout_button' );
		// Hide title.
		add_filter( 'give_form_title', '__return_empty_string' );
	}


	/**
	 * @inheritDoc
	 */
	public function loadScripts() {
		if ( $this->scriptsLoaded ) {
			return;
		}

		$this->scriptsLoaded = true;

		// Font
		$primaryFont = $this->options[ 'appearance' ][ 'primary_font' ];

		if ( in_array( $primaryFont, [ 'custom', 'montserrat' ] ) ) {
			$font = ( 'montserrat' === $primaryFont )
				? 'Montserrat'
				: $this->options[ 'appearance' ][ 'custom_font' ];

			wp_enqueue_style(
				'give-google-font',
				"https://fonts.googleapis.com/css?family={$font}:400,500,600,700&display=swap",
				[],
				GIVE_VERSION
			);
		}

		// If default Give styles are disabled globally, enqueue Give default styles here
		if ( ! give_is_setting_enabled( give_get_option( 'css' ) ) ) {
			wp_enqueue_style(
				'give-styles',
				( new Give_Scripts )->get_frontend_stylesheet_uri(),
				[],
				GIVE_VERSION
			);
		}

		// Form styles
		wp_enqueue_style(
			'give-classic-template',
			GIVE_PLUGIN_URL . 'assets/dist/css/give-classic-template.css',
			[ 'give-styles' ],
			GIVE_VERSION
		);

		// CSS Variables
		wp_add_inline_style(
			'give-classic-template',
			$this->loadFile( 'css/variables.php', [
				'primaryColor' => $this->options[ 'appearance' ][ 'primary_color' ]
			] )
		);

		// Inline CSS
		wp_add_inline_style(
			'give-classic-template',
			$this->loadFile( 'css/inline.css' )
		);

		// JS
		wp_enqueue_script(
			'give-classic-template-js',
			GIVE_PLUGIN_URL . 'assets/dist/js/give-classic-template.js',
			[ 'give' ],
			GIVE_VERSION,
			true
		);

		wp_localize_script(
			'give-classic-template-js',
			'classicTemplateOptions',
			$this->options
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getLoadingView() {
		return $this->loadFile( 'views/loading.php', [
			'options' => $this->options[ 'appearance' ]
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
	 * Render donation form header
	 */
	public function renderHeader() {
		echo $this->loadFile( 'views/header.php', [
			'options' => $this->options[ 'appearance' ]
		] );
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
