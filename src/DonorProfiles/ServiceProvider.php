<?php

namespace Give\DonorProfiles;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;
use Give\DonorProfiles\Shortcode as Shortcode;
use Give\DonorProfiles\Block as Block;
use Give\DonorProfiles\App as App;
use Give\DonorProfiles\Profile as Profile;
use Give\DonorProfiles\Routes\DonationsRoute;
use Give\DonorProfiles\Routes\ProfileRoute;
use Give\DonorProfiles\Routes\LocationRoute;
use Give\DonorProfiles\Routes\LoginRoute;
use Give\DonorProfiles\Routes\LogoutRoute;
use Give\DonorProfiles\Routes\VerifyEmailRoute;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {

		give()->singleton( App::class );
		give()->singleton( Shortcode::class );

		give()->bind( DonationsRoute::class );
		give()->bind( ProfileRoute::class );
		give()->bind( LocationRoute::class );
		give()->bind( LoginRoute::class );
		give()->bind( LogoutRoute::class );

		if ( give_is_setting_enabled( give_get_option( 'email_access' ) ) ) {
			give()->bind( VerifyEmailRoute::class );
		}

		if ( function_exists( 'register_block_type' ) ) {
			give()->singleton( Block::class );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'init', Shortcode::class, 'addShortcode' );
		Hooks::addAction( 'wp_enqueue_scripts', Shortcode::class, 'loadFrontendAssets' );
		Hooks::addAction( 'rest_api_init', DonationsRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', ProfileRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', LocationRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', LoginRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', LogoutRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', VerifyEmailRoute::class, 'registerRoute' );

		if ( function_exists( 'register_block_type' ) ) {
			Hooks::addAction( 'init', Block::class, 'addBlock' );
			Hooks::addAction( 'wp_enqueue_scripts', Block::class, 'loadFrontendAssets' );
			Hooks::addAction( 'enqueue_block_editor_assets', Block::class, 'loadEditorAssets' );
		}
	}
}
