<?php
namespace Give\Tracking;

use Give_Admin_Settings;

/**
 * Class AdminSettings
 * @package Give\Tracking
 *
 * @since 2.10.0
 */
class AdminSettings {
	/**
	 *
	 */
	public function register() {
		add_filter( 'give_get_settings_advanced', [ $this, 'addSettings' ] );
	}

	/**
	 * @param array $settings
	 *
	 * @return mixed
	 */
	public function addSettings( $settings ) {
		if ( ! Give_Admin_Settings::is_setting_page( 'advanced', 'advanced-options' ) ) {
			return $settings;
		}

		array_splice(
			$settings,
			-2,
			0,
			[
				[
					'name'    => __( 'Usage Tracking', 'give' ),
					'label'   => esc_html__( 'Allow usage of GiveWP to be tracked.', 'give' ),
					'desc'    => sprintf(
						'%1$s <a href="%2$s">%3$s</a>',
						esc_html__( 'To opt out, choose "Disabled". Your website remains untracked, and no data will be collected. Read about what usage data is tracked at:', 'give' ),
						'#',
						esc_html__( 'GiveWP.com Usage Tracking Documentation', 'give' )
					),
					'id'      => 'usage_tracking',
					'type'    => 'radio_inline',
					'default' => 'enabled',
					'options' => [
						'enabled'  => esc_html__( 'Enabled', 'give' ),
						'disabled' => esc_html__( 'Disabled', 'give' ),
					],
				],
			]
		);

		error_log( print_r( $settings, true ) . "\n", 3, WP_CONTENT_DIR . '/debug_new.log' );

		return $settings;
	}
}
