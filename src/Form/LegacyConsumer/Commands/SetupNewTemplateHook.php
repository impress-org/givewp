<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FieldCollection;
use Give\Form\LegacyConsumer\FieldView;

class SetupNewTemplateHook implements HookCommandInterface {
    public function __invoke( $hook ) {
        $fieldCollection = new FieldCollection( 'root' );
        do_action( "give_fields_$hook", $fieldCollection );
        add_action( "give_$hook", function( $formID ) use ( $fieldCollection ) {
            foreach( $fieldCollection->getFields() as $field ) {
                FieldView::render( $field );
            }
        });
    }
}
