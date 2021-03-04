<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Form\LegacyConsumer\TemplateHooks;
use Give\Form\LegacyConsumer\Commands\SetupFieldPersistance;

final class SetupFieldPersistanceTest extends Give_Unit_Test_Case {

    public function testFieldPersistance() {

        add_action( 'give_fields_donation_form', function( $fieldCollection ) {
            $fieldCollection->append(
                ( new FormField( 'text', 'my-text-field' ) )
                    ->emailTag( 'myTextField' )
            );
        });

        $_POST[ 'my-text-field' ] = 'foobar';

        $paymentID = Give_Helper_Payment::create_simple_payment();

        $this->assertEquals(
            'foobar',
            give_get_payment_meta( $paymentID, 'my-text-field', $single = true )
        );
    }
}
