<?php

namespace Give\DonorDashboards\Admin;

/**
 * @since 2.10.0
 */
class Settings {

	/**
	 * Register settings related to Donor Profiles
	 *
	 * @param array $settings
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function register( $settings ) {

		$donorDashboardSettings = [
			$this->getDonorDashboardPageSetting(),
			$this->donorDashboardPageIsPublished() ? $this->getOverrideLegacyDonationManagementPagesSetting() : null,
		];

		return give_settings_array_insert(
			$settings,
			'history_page',
			$donorDashboardSettings
		);
	}

	/**
	 * Return true if donor profile page is defined and published, false if not
	 *
	 * @return boolean
	 *
	 * @since 2.10.0
	 */
	protected function donorDashboardPageIsPublished() {
		$donorDashboardPageId = ! empty( give_get_option( 'donor_dashboard_page' ) ) ? give_get_option( 'donor_dashboard_page' ) : null;
		return $donorDashboardPageId && get_post_status( $donorDashboardPageId ) === 'publish';
	}

	/**
	 * Return CMB2 compatible array used to render/control donor profile page setting
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function getDonorDashboardPageSetting() {

		$generateDonorDashboardPageUrl = add_query_arg(
			[
				'give-generate-donor-dashboard-page' => '1',
			],
			admin_url( 'edit.php' )
		);

		$generateDonorDashboardPageDesc = $this->donorDashboardPageIsPublished() ? '' : sprintf( __( ' Need helping setting one up? <a href="%s">Generate a new Donor Dashboard page.</a>', 'give' ), $generateDonorDashboardPageUrl );

		return [
			'name'       => __( 'Donor Dashboard Page', 'give' ),
			'desc'       => __( 'This is the page where donors can manage their information, review history and more -- all in one place. The Donor Dashboard block or <code>[give_donor_dashboard]</code> shortcode should be on this page. ', 'give' ) . $generateDonorDashboardPageDesc,
			'id'         => 'donor_dashboard_page',
			'type'       => 'select',
			'class'      => 'give-select give-select-chosen',
			'options'    => give_cmb2_get_post_options(
				[
					'post_type'   => 'page',
					'numberposts' => 30,
				]
			),
			'attributes' => [
				'data-search-type' => 'pages',
				'data-placeholder' => esc_html__( 'Choose a page', 'give' ),
			],
		];
	}

	/**
	 * Return CMB2 compatible array used to render/control override legacy donation manamgent pages setting
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function getOverrideLegacyDonationManagementPagesSetting() {
		return [
			'name'          => esc_html__( 'Override Legacy Donation Management Pages', 'give' ),
			'desc'          => esc_html__( 'Use Donor Dashboard in favor of legacy donation management pages (Donation History, Edit Proifle, Subscriptions, etc).', 'give' ),
			'id'            => 'override_legacy_donation_management_pages',
			'wrapper_class' => 'override-legacy-donation-management-pages',
			'type'          => 'radio_inline',
			'default'       => 'enabled',
			'options'       => [
				'enabled'  => esc_html__( 'Enabled', 'give' ),
				'disabled' => esc_html__( 'Disabled', 'give' ),
			],
		];
	}

	/**
	 * Generate donor profile page, and update site setting to use it
	 *
	 * @return void
	 *
	 * @since 2.10.0
	 */
	public function generateDonorDashboardPage() {

		$content = $this->getDonorDashboardPageContent( 'block' );

		$pageId = wp_insert_post(
			[
				'comment_status' => 'close',
				'ping_status'    => 'close',
				'post_author'    => 1,
				'post_title'     => __( 'Donor Dashboard', 'give' ),
				'post_status'    => 'publish',
				'post_content'   => $content,
				'post_type'      => 'page',
			]
		);

		if ( $pageId ) {

			give_update_option( 'donor_dashboard_page', $pageId );

			give_update_option( 'override_legacy_donation_management_pages', 'enabled' );

			$overrideSettingsMap = [
				'history_page',
				'subscriptions_page',
			];

			foreach ( $overrideSettingsMap as $setting ) {
				if ( give_get_option( $setting ) !== $pageId ) {
					give_update_option( $setting, $pageId );
				}
			}
		}

	}

	/**
	 * Get default content for donor profile page, based on format (block vs shortcode)
	 *
	 * @param string $format
	 * @return string
	 *
	 * @since 2.10.0
	 */
	protected function getDonorDashboardPageContent( $format ) {

		switch ( $format ) {
			case 'block': {
				return get_comment_delimited_block_content(
					'give/donor-dashboard',
					[
						'align' => 'wide',
					],
					null
				);
			}
			case 'shortcode': {
				return '[give_donor_dashboard]';
			}
			default: {
				return null;
			}
		}

	}

	/**
	 * Filter and override legacy donation management page settings
	 *
	 * @param array $settings
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function overrideLegacyDonationManagementPageSettings( $settings ) {

		// Only override settings if the the override legacy donation management pages setting is enabled
		if ( $this->donorDashboardPageIsPublished() && give_is_setting_enabled( give_get_option( 'override_legacy_donation_management_pages', 'enabled' ) ) ) {

			$pageId = give_get_option( 'donor_dashboard_page' );

			$overrideSettingsMap = [
				'history_page',
				'subscriptions_page',
			];

			foreach ( $overrideSettingsMap as $setting ) {
				if ( give_get_option( $setting ) !== $pageId ) {
					give_update_option( $setting, $pageId );
				}
			}

			// Hide settings that are overriden by Donor Profile setting
			$key = 0;
			foreach ( $settings as $setting ) {
				if ( in_array( $setting['id'], $overrideSettingsMap ) ) {
					unset( $settings[ $key ] );
				}
				$key++;
			}
		}

		return $settings;
	}

}
