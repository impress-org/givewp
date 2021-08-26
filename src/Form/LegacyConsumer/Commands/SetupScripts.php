<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;

/**
 * @unreleased
 */
class SetupScripts {
	/**
	 * @var int
	 */
	private $formId;

	/**
	 * @unreleased
	 * @param int $formId
	 */
	public function __construct( $formId ) {
		$this->formId = $formId;
	}

	/**
	 * @unreleased
	 */
	public function __invoke( $initial, $hook ) {
		$collection = Group::make( $hook );
		do_action( "give_fields_$hook", $collection, $this->formId );

		$registerDateScripts = false;

		$collection->walkFields(
		/* @var File $field */
			function( $field ) use ( &$registerDateScripts  ) {
				if( 'date' === $field->getType() ) {
					$registerDateScripts = true;
				}
			}
		);

		if( $registerDateScripts ) {
			wp_enqueue_script(
				'give-date-field-js',
				GIVE_PLUGIN_URL . '/assets/dist/js/give-date-field.js',
				[ 'jquery-ui-datepicker' ],
				GIVE_VERSION,
				true
			);

			wp_enqueue_style(
				'give-date-field-css',
				GIVE_PLUGIN_URL . '/assets/dist/css/give-date-field.css',
				[],
				GIVE_VERSION,
				true
			);
		}
	}
}
