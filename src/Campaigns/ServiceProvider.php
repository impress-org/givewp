<?php

namespace Give\Campaigns;

use Give\Helpers\Hooks;
use Give\Campaigns\AdminPageView;

class ServiceProvider implements \Give\ServiceProviders\ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->bind(
			AdminPageView::class,
			function ( $app ) {
				return new AdminPageView( plugin_dir_path( __FILE__ ) . 'resources/templates/' );
			}
		);
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		include_once plugin_dir_path( __FILE__ ) . 'functions.php';
		add_action( 'admin_menu', [ $this, 'registerMenus' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ] );
	}

	public function registerMenus() {
		add_submenu_page(
			'edit.php?post_type=give_forms',
			esc_html__( 'Campaigns', 'give' ),
			esc_html__( 'Campaigns', 'give' ),
			'manage_give_settings',
			'give-campaigns',
			[ give()->make( AdminPageView::class ), 'render' ],
			$position = 0
		);
	}

	public function adminEnqueueScripts() {

		$data = give_campaings_get_aggregate_total_query();

		$appURL = GIVE_PLUGIN_URL . 'assets/dist/js/campaigns.js';
		wp_enqueue_script( 'give-campaigns-app', $appURL, [ 'react', 'react-dom', 'wp-components', 'wp-element', 'wp-polyfill' ], GIVE_VERSION, $in_footer = true );

		wp_localize_script(
			'give-campaigns-app',
			'giveCampaigns',
			[
				'campaings' => [
					[
						'title'    => 'My First Campaign',
						'progress' => $data->total / 100000,
						'meta'     => [
							'raised'    => '$' . number_format( $data->total ),
							'donations' => $data->count,
							'goal'      => '$100,000',
						],
					],
				],
			]
		);
	}
}
