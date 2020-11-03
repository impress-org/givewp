<?php

namespace Give\DonorProfiles;

use Give\DonorProfiles\Model as DonorProfile;

class Block {

	protected $donorProfile;

	public function __construct() {
		$this->donorProfile = give( DonorProfile::class );
	}

	/**
	 * Registers Donor Profile block
	 *
	 * @since 2.9.0
	 **/
	public function addBlock() {
		register_block_type(
			'give/donor-profile',
			[
				'render_callback' => [ $this, 'renderCallback' ],
				'attributes'      => [
					'enabled' => [
						'type'    => 'array',
						'default' => [],
					],
				],
			]
		);
	}

	/**
	 * Returns Donor Profile block markup
	 *
	 * @since 2.9.0
	 **/
	public function renderCallback( $attributes ) {
		return $this->donorProfile->getOutput( $attributes );
	}

	/**
	 * Load Donor Profile frontend assets
	 *
	 * @since 2.9.0
	 **/
	public function loadFrontendAssets() {
		if ( has_block( 'give/donor-profile' ) ) {
			return $this->donorProfile->loadAssets();
		}
	}

	/**
	 * Load Donor Profile block editor assets
	 *
	 * @since 2.9.0
	 **/
	public function loadEditorAssets() {
		wp_enqueue_script(
			'give-donor-profiles-block',
			GIVE_PLUGIN_URL . 'assets/dist/js/donor-profiles-block.js',
			[],
			GIVE_VERSION,
			true
		);
	}
}
