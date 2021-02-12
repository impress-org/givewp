<?php

namespace Give\DonorProfiles;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;
use Give\DonorProfiles\Shortcode as Shortcode;
use Give\DonorProfiles\Block as Block;
use Give\DonorProfiles\App as App;

use Give\DonorProfiles\Tabs\ProfileTab\Tab as ProfileTab;
use Give\DonorProfiles\Tabs\DonationHistoryTab\Tab as DonationHistoryTab;

use Give\DonorProfiles\Tabs\TabsRegister;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {

		give()->singleton( 'donorProfileTabs', TabsRegister::class );

		give()->singleton( App::class );
		give()->singleton( Shortcode::class );

		if ( function_exists( 'register_block_type' ) ) {
			give()->singleton( Block::class );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'init', Shortcode::class, 'addShortcode' );

		// Register Tabs
		Hooks::addAction( 'init', ProfileTab::class, 'registerTab' );
		Hooks::addAction( 'init', DonationHistoryTab::class, 'registerTab' );

		Hooks::addAction( 'wp_enqueue_scripts', TabsRegister::class, 'enqueueTabAssets' );
		Hooks::addAction( 'rest_api_init', TabsRegister::class, 'registerTabRoutes' );

		Hooks::addAction( 'wp_enqueue_scripts', Shortcode::class, 'loadFrontendAssets' );

		if ( function_exists( 'register_block_type' ) ) {
			Hooks::addAction( 'init', Block::class, 'addBlock' );
			Hooks::addAction( 'wp_enqueue_scripts', Block::class, 'loadFrontendAssets' );
			Hooks::addAction( 'enqueue_block_editor_assets', Block::class, 'loadEditorAssets' );
		}
	}
}
