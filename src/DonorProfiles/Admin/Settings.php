<?php

namespace Give\DonorProfiles\Admin;

class Settings {

	public function register( $settings ) {
		$donorProfileSettings = [
			$this->getDonorProfilePageSetting(),
			$this->getOverrideLegacyDonationManagementPagesSetting(),
		];

		return give_settings_array_insert(
			$settings,
			'base_country',
			$donorProfileSettings
		);
	}

	protected function getDonorProfilePageSetting() {
		return [
			'name'       => __( 'Donor Profile Page', 'give' ),
			'desc'       => __( 'This is the page donors can access to manage their subscriptions. The <code>[give_subscriptions]</code> shortcode should be on this page.', 'give' ),
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

	protected function getOverrideLegacyDonationManagementPagesSetting() {
		return [
			'name'          => esc_html__( 'Override Legacy Donation Managment Pages', 'give' ),
			'desc'          => esc_html__( 'Use Donor Profile Page setting to override legacy donation management pages.', 'give' ),
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


}
