<?php

namespace Give\DonorDashboards;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;
use Give\DonorDashboards\Shortcode as Shortcode;
use Give\DonorDashboards\Block as Block;
use Give\DonorDashboards\App as App;
use Give\DonorDashboards\RequestHandler as RequestHandler;

use Give\DonorDashboards\Profile as Profile;

use Give\DonorDashboards\Routes\LoginRoute;
use Give\DonorDashboards\Routes\LogoutRoute;
use Give\DonorDashboards\Routes\VerifyEmailRoute;

use Give\DonorDashboards\Tabs\DonationHistoryTab\Tab as DonationHistoryTab;
use Give\DonorDashboards\Tabs\EditProfileTab\Tab as EditProfileTab;

use Give\DonorDashboards\Tabs\TabsRegister;

use Give\DonorDashboards\Admin\UpgradeNotice;
use Give\DonorDashboards\Admin\SuccessNotice;
use Give\DonorDashboards\Admin\Settings;

/**
 * @since 2.10.0
 */
class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {

		give()->singleton( 'donorDashboardTabs', TabsRegister::class );
		give()->singleton( 'donorDashboard', Profile::class );

	}

	/**
	 * @inheritDoc
	 */
	public function boot() {

		Hooks::addAction( 'give_after_install', Settings::class, 'generateDonorDashboardPage' );

		Hooks::addAction( 'admin_notices', UpgradeNotice::class, 'register' );
		Hooks::addAction( 'admin_notices', SuccessNotice::class, 'register' );

		Hooks::addFilter( 'give_settings_general', Settings::class, 'register' );
		Hooks::addFilter( 'give_settings_general', Settings::class, 'overrideLegacyDonationManagementPageSettings', 999 );

		Hooks::addAction( 'give_embed_head', App::class, 'loadAssets', 2 );

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
