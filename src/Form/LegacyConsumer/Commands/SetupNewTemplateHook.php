<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

/**
 * @unreleased
 */
class SetupNewTemplateHook implements HookCommandInterface {

	/**
	 * @unreleased
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {

		// On the old hook, run the new hook and render the fields.
		add_action(
			"give_$hook",
			function( $formID ) use ( $hook ) {
				$fieldCollection = new FieldCollection( 'root' );
				do_action( "give_fields_$hook", $fieldCollection, $formID );
				foreach ( $fieldCollection->getFields() as $field ) {
					FieldView::render( $field );
				}
			}
		);
	}
}
