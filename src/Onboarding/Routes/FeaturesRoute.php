<?php

namespace Give\Onboarding\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\Onboarding\SettingsRepositoryFactory;

/**
 * @since 2.8.0
 */
class FeaturesRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'onboarding/settings/features';

	/**
	 * @var SettingsRepository
	 */
	protected $settingsRepository;

	/**
	 * @param SettingsRepository $settingsRepository
	 *
	 * @since 2.8.0
	 */
	public function __construct( SettingsRepositoryFactory $settingsRepositoryFactory ) {
		$this->settingsRepository = $settingsRepositoryFactory->make( 'give_onboarding' );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function handleRequest( WP_REST_Request $request ) {

		$features = json_decode( $request->get_param( 'value' ) );

		$formID = $this->settingsRepository->get( 'form_id' );

		update_post_meta( $formID, '_give_goal_option', in_array( 'donation-goal', $features ) ? 'enabled' : 'disabled' );
		update_post_meta( $formID, '_give_donor_comment', in_array( 'donation-comments', $features ) ? 'enabled' : 'disabled' );
		update_post_meta( $formID, '_give_terms_option', in_array( 'terms-conditions', $features ) ? 'enabled' : 'disabled' );
		update_post_meta( $formID, '_give_customize_offline_donations', in_array( 'offline-donations', $features ) ? 'enabled' : 'disabled' );
		update_post_meta( $formID, '_give_anonymous_donation', in_array( 'anonymous-donations', $features ) ? 'enabled' : 'disabled' );
		update_post_meta( $formID, '_give_company_field', in_array( 'company-donations', $features ) ? 'optional' : 'disabled' ); // Note: The company field has two values for enabled, "required" and "optional".

		return [
			'data' => [
				'setting' => 'features',
				'value'   => $features,
				'formID'  => $formID,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			$this->endpoint,
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => function() {
						return current_user_can( 'manage_options' );
					},
					'args'                => [
						'value' => [
							'type'              => 'string',
							'required'          => true,
							// 'validate_callback' => [ $this, 'validateSetting' ],
							'sanitize_callback' => 'sanitize_text_field',
						],
					],
				],
				'schema' => [ $this, 'getSchema' ],
			]
		);
	}


	/**
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function getSchema() {
		return [
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'onboarding',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => [
				'setting' => [
					'description' => esc_html__( 'The reference name for the setting being updated.', 'give' ),
					'type'        => 'string',
				],
				'value'   => [
					'description' => esc_html__( 'The value of the setting being updated.', 'give' ),
					'type'        => 'string',
				],
			],
		];
	}
}
