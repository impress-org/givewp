<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Form\LegacyConsumer\FieldView;
use Give\Framework\FieldsAPI\Group;

/**
 * @since 2.10.2
 */
class SetupNewTemplateHook implements HookCommandInterface {

	/**
	 * @since 2.10.2
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {
		// On the old hook, run the new hook and render the fields.
		add_action(
			"give_$hook",
			static function ( $formId ) use ( $hook ) {
				$collection = Group::make( $hook );
				do_action( "give_fields_$hook", $collection, $formId );
				$collection->walk(
					static function ( $node ) use ( $formId ) {
						FieldView::render( $node, $formId );
					}
				);
			}
		);
	}
}
