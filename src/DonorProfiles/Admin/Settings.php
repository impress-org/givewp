<?php

namespace Give\DonorProfiles\Admin;

class Settings {

	public function register( $settings ) {

		$donorProfilePageIsSet = empty( give_get_option( 'donor_profile_page' ) ) ? false : true;

		$donorProfileSettings = [
			$this->getDonorProfilePageSetting(),
			$donorProfilePageIsSet ? $this->getOverrideLegacyDonationManagementPagesSetting() : null,
		];

		return give_settings_array_insert(
			$settings,
			'history_page',
			$donorProfileSettings
		);
	}

	protected function getDonorProfilePageSetting() {
		return [
			'name'       => __( 'Donor Profile Page', 'give' ),
			'desc'       => __( 'This is the page where donors can manage their information, review history and more -- all in one place. The Donor Profile block or <code>[give_donor_profile]</code> shortcode should be on this page. Need helping setting one up? Let us <a href="#">generate one for you.</a>', 'give' ),
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


}
