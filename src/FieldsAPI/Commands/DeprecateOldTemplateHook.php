<?php

namespace Give\FieldsAPI\Commands;

use Give\FieldsAPI\FilterCallbackCollection;

class DeprecateOldTemplateHook implements HookCommandInterface {

    public function __invoke( $hook ) {
        global $wp_filter;
        if( has_filter( "give_$hook" ) ) {
            $callbacks = FilterCallbackCollection::make(
                    $wp_filter[ "give_$hook" ]->callbacks
                )
                ->flatten()
                ->withoutPrefix( 'give_' );

            if( $callbacks->count() > 1 ) {
                _give_deprecated_function( sprintf( __( 'The %s action' ), "give_$hook" ), '2.10', "give_fields_$hook" );
            }
        }
    }
}
