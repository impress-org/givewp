<?php

namespace Give\DonorProfiles;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;
use Give\DonorProfiles\Shortcode as DonorProfileShortcode;
use Give\DonorProfiles\Block as DonorProfileBlock;
use Give\DonorProfiles\Model as DonorProfile;
use Give\DonorProfiles\Routes\DonationsRoute;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( DonorProfile::class );

		give()->singleton( DonorProfileShortcode::class );

		give()->bind( DonationsRoute::class );

		if ( function_exists( 'register_block_type' ) ) {
			give()->singleton( DonorProfileBlock::class );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'init', DonorProfileShortcode::class, 'addShortcode' );
		Hooks::addAction( 'wp_enqueue_scripts', DonorProfileShortcode::class, 'loadFrontendAssets' );
		Hooks::addAction( 'rest_api_init', DonationsRoute::class, 'registerRoute' );

		if ( function_exists( 'register_block_type' ) ) {
			Hooks::addAction( 'init', DonorProfileBlock::class, 'addBlock' );
			Hooks::addAction( 'wp_enqueue_scripts', DonorProfileBlock::class, 'loadFrontendAssets' );
			Hooks::addAction( 'enqueue_block_editor_assets', DonorProfileBlock::class, 'loadEditorAssets' );
		}
	}
}
