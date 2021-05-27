<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Form\LegacyConsumer\FilterCallbackCollection;

/**
 * @since 2.10.2
 */
class DeprecateOldTemplateHook implements HookCommandInterface {
	/**
	 * @since 2.10.2
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {
		global $wp_filter;
		if ( has_filter( "give_$hook" ) ) {
			$callbacks = FilterCallbackCollection::make(
				$wp_filter[ "give_$hook" ]->callbacks
			)
				->flatten()
				->withoutPrefix( 'give_' );

			if ( $callbacks->count() > 1 ) {
				_give_deprecated_function( sprintf( __( 'The %s action', 'give' ), "give_$hook" ), '2.10', "give_fields_$hook" );
			}
		}
	}
}
