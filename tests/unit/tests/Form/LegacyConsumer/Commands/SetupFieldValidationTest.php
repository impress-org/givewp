<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Form\LegacyConsumer\TemplateHooks;
use Give\Form\LegacyConsumer\Commands\SetupFieldPersistance;

final class SetupFieldValidationTest extends Give_Unit_Test_Case {

    public function testFieldValidation() {

        add_action( 'give_fields_donation_form', function( $fieldCollection ) {

            $fieldCollection->append(
                ( new FormField( 'text', 'my-optional-field' ) )
            );

            $fieldCollection->append(
                ( new FormField( 'text', 'my-required-field' ) )
                    ->required()
            );
        });

        $required_fields = apply_filters( 'give_donation_form_required_fields', [], 0 );

        $this->assertArrayNotHasKey(
            'my-optional-field',
            $required_fields
        );

        $this->assertArrayHasKey(
            'my-required-field',
            $required_fields
        );
    }
}
