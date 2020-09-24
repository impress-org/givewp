<?php

namespace Give\Campaigns;

use Give\Helpers\Hooks;

class ServiceProvider implements \Give\ServiceProviders\ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		// ...
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		include_once plugin_dir_path( __FILE__ ) . 'functions.php';
		add_action( 'init', [ $this, 'registerPostTypes' ] );
		add_action( 'init', [ $this, 'initBlocks' ] );
		add_action(
			'enqueue_block_editor_assets',
			function() {
				wp_enqueue_style( 'campaigns-cpt-styles', GIVE_PLUGIN_URL . 'assets/dist/css/campaigns-cpt.css' );
			}
		);
	}

	public function registerPostTypes() {
		register_post_type(
			'give_campaign',
			include 'config/give_campaigns.cpt.php'
		);
	}

	public function initBLocks() {

		wp_register_script(
			'give-block-campaigns',
			GIVE_PLUGIN_URL . 'assets/dist/js/campaigns-block.js',
			[],
			false,
			$footer = true
		);

		$editorColorPalette = get_theme_support( 'editor-color-palette' ); // Return value is in a nested array.
		wp_localize_script(
			'give-block-campaigns',
			'giveCampaignsThemeSupport',
			[
				'editorColorPalette' => array_shift( $editorColorPalette ),
			]
		);

		register_block_type(
			'give/campaign-preview',
			[
				'editor_script' => 'give-block-campaigns',
			]
		);

		register_block_type(
			'give/campaign-progress-bar',
			[
				'editor_script' => 'give-block-campaigns',
			]
		);

		register_block_type(
			'give/campaign-featured-image',
			[
				'editor_script' => 'give-block-campaigns',
			]
		);
	}
}
