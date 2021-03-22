<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Form\LegacyConsumer\TemplateHooks;
use Give\Form\LegacyConsumer\Commands\SetupFieldPersistance;

final class SetupPaymentDetailsDisplayTest extends Give_Unit_Test_Case {

    public function testDisplaysDonationMeta() {

        add_action( 'give_fields_donation_form', function( $fieldCollection ) {
            $fieldCollection->append(
                ( new FormField( 'text', 'my-text-field' ) )
                    ->label( 'My Text Field' )
            );
        });

        $paymentID = Give_Helper_Payment::create_simple_payment();
        give_update_payment_meta( $paymentID, 'my-text-field','foobar' );

        ob_start();
        do_action( 'give_view_donation_details_billing_after', $paymentID );
        $output = ob_get_clean();

        $this->assertContains( 'foobar', $output, 'Output does not contain custom field value.' );
        $this->assertContains( 'My Text Field', $output, 'Output does not contain custom field label.' );
    }

    public function testNotDisplaysDonorMeta() {

        add_action( 'give_fields_donation_form', function( $fieldCollection ) {
            $fieldCollection->append(
                ( new FormField( 'text', 'my-text-field' ) )
                    ->label( 'My Text Field' )
                    ->storeAsDonorMeta()
            );
        });

        $paymentID = Give_Helper_Payment::create_simple_payment();
        give_update_payment_meta( $paymentID, 'my-text-field','foobar' );

        ob_start();
        do_action( 'give_view_donation_details_billing_after', $paymentID );
        $output = ob_get_clean();

        $this->assertNotContains( 'foobar', $output, 'Donation details contain donor meta value.' );
        $this->assertNotContains( 'My Text Field', $output, 'Donation details contain donor meta label.' );
    }
}
