<?php

namespace Give\DonorProfiles;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;
use Give\DonorProfiles\Shortcode as Shortcode;
use Give\DonorProfiles\Block as Block;
use Give\DonorProfiles\App as App;
use Give\DonorProfiles\RequestHandler as RequestHandler;

use Give\DonorProfiles\Profile as Profile;

use Give\DonorProfiles\Routes\LoginRoute;
use Give\DonorProfiles\Routes\LogoutRoute;
use Give\DonorProfiles\Routes\VerifyEmailRoute;

use Give\DonorProfiles\Tabs\DonationHistoryTab\Tab as DonationHistoryTab;
use Give\DonorProfiles\Tabs\EditProfileTab\Tab as EditProfileTab;

use Give\DonorProfiles\Tabs\TabsRegister;

use Give\DonorProfiles\Admin\UpgradeNotice;
use Give\DonorProfiles\Admin\Settings;

/**
 * @since 2.10.0
 */
class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {

		give()->singleton( 'donorProfileTabs', TabsRegister::class );
		give()->singleton( 'donorProfile', Profile::class );

	}

	/**
	 * @inheritDoc
	 */
	public function boot() {

		Hooks::addAction( 'admin_notices', UpgradeNotice::class, 'register' );
		Hooks::addFilter( 'give_settings_general', Settings::class, 'register' );
		Hooks::addFilter( 'give_settings_general', Settings::class, 'overrideLegacyDonationManagementPageSettings', 999 );

		Hooks::addAction( 'give_embed_head', App::class, 'loadAssets' );

		Hooks::addFilter( 'query_vars', RequestHandler::class, 'filterQueryVars' );
		Hooks::addAction( 'parse_request', RequestHandler::class, 'parseRequest' );

		Hooks::addAction( 'init', Shortcode::class, 'addShortcode' );

		Hooks::addAction( 'rest_api_init', LoginRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', LogoutRoute::class, 'registerRoute' );

		if ( give_is_setting_enabled( give_get_option( 'email_access' ) ) ) {
			Hooks::addAction( 'rest_api_init', VerifyEmailRoute::class, 'registerRoute' );
		}

		if ( function_exists( 'register_block_type' ) ) {
			Hooks::addAction( 'init', Block::class, 'addBlock' );
			Hooks::addAction( 'enqueue_block_editor_assets', Block::class, 'loadEditorAssets' );
		}

		// Register Tabs
		Hooks::addAction( 'init', DonationHistoryTab::class, 'registerTab' );
		Hooks::addAction( 'init', EditProfileTab::class, 'registerTab' );

		Hooks::addAction( 'give_embed_head', TabsRegister::class, 'enqueueTabAssets' );
		Hooks::addAction( 'rest_api_init', TabsRegister::class, 'registerTabRoutes' );

	}
}
