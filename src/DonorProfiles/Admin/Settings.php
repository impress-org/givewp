<?php

namespace Give\DonorProfiles\Admin;

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

		$donorProfileSettings = [
			$this->getDonorProfilePageSetting(),
			$this->donorProfilePageIsPublished() ? $this->getOverrideLegacyDonationManagementPagesSetting() : null,
		];

		return give_settings_array_insert(
			$settings,
			'history_page',
			$donorProfileSettings
		);
	}

	/**
	 * Return true if donor profile page is defined and published, false if not
	 *
	 * @return boolean
	 *
	 * @since 2.10.0
	 */
	protected function donorProfilePageIsPublished() {
		$donorProfilePageId = ! empty( give_get_option( 'donor_profile_page' ) ) ? give_get_option( 'donor_profile_page' ) : null;
		return $donorProfilePageId && get_post_status( $donorProfilePageId ) === 'publish';
	}

	/**
	 * Return CMB2 compatible array used to render/control donor profile page setting
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function getDonorProfilePageSetting() {

		$generateDonorProfilePageUrl = add_query_arg(
			[
				'give-generate-donor-profile-page' => '1',
			],
			admin_url( 'edit.php' )
		);

		return [
			'name'       => __( 'Donor Profile Page', 'give' ),
			'desc'       => sprintf( __( 'This is the page where donors can manage their information, review history and more -- all in one place. The Donor Profile block or <code>[give_donor_profile]</code> shortcode should be on this page. Need helping setting one up? Let us <a href="%s">generate one for you.</a>', 'give' ), $generateDonorProfilePageUrl ),
			'id'         => 'donor_profile_page',
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
			'name'          => esc_html__( 'Override Legacy Donation Managment Pages', 'give' ),
			'desc'          => esc_html__( 'Use Donor Profile in favor of legacy donation management pages (Donation History, Edit Proifle, Subscriptions, etc).', 'give' ),
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
	public function generateDonorProfilePage() {

		$content = $this->getDonorProfilePageContent( 'block' );

		$pageId = wp_insert_post(
			[
				'comment_status' => 'close',
				'ping_status'    => 'close',
				'post_author'    => 1,
				'post_title'     => __( 'Donor Profile', 'give' ),
				'post_status'    => 'publish',
				'post_content'   => $content,
				'post_type'      => 'page',
			]
		);

		if ( $pageId ) {

			give_update_option( 'donor_profile_page', $pageId );

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
	protected function getDonorProfilePageContent( $format ) {

		switch ( $format ) {
			case 'block': {
				return get_comment_delimited_block_content(
					'give/donor-profile',
					[
						'align' => 'wide',
					],
					null
				);
			}
			case 'shortcode': {
				return '[give_donor_profile]';
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
		if ( $this->donorProfilePageIsPublished() && give_is_setting_enabled( give_get_option( 'override_legacy_donation_management_pages', 'enabled' ) ) ) {

			$pageId = give_get_option( 'donor_profile_page' );

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
