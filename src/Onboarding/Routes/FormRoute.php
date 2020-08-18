<?php

namespace Give\Onboarding\Routes;

use WP_REST_Request;
use Give\API\RestRoute;
use Give\Onboarding\FormRepository;

/**
 * @since 2.8.0
 */
class FormRoute implements RestRoute {

	/** @var string */
	protected $endpoint = 'onboarding/form';

	/** @var FormRepository */
	protected $formRepository;

	/**
	 * @param FormRepository $formRepository
	 *
	 * @since 2.8.0
	 */
	public function __construct( FormRepository $formRepository ) {
		$this->formRepository = $formRepository;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function handleRequest( WP_REST_Request $request ) {
		return [
			'formID' => $this->formRepository->getOrMake(),
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
				// ...
			],
		];
	}
}
