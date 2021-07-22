<?php

use Give\Framework\FieldsAPI\Text;

final class SetupFieldValidationTest extends Give_Unit_Test_Case {

    public function testFieldValidation() {

        add_action( 'give_fields_donation_form', function( $form ) {

            $form->append(
            	Text::make( 'my-optional-field' ),
	            Text::make( 'my-required-field' ) ->required()
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
