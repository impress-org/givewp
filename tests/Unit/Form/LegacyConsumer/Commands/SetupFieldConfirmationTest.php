<?php

namespace Give\Tests\Unit\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\Text;
use Give_Helper_Payment;
use Give\Tests\TestCase;

final class SetupFieldConfirmationTest extends TestCase
{


    /*
    |--------------------------------------------------------------------------
    | Donation Meta
    |--------------------------------------------------------------------------
    */

    public function testConfirmationHasDonationMeta()
    {
        add_action('give_fields_donation_form', function ($form) {
            $form->append(
                Text::make('my-text-field')
                    ->label('My Text Field')
                    ->showInReceipt()
            );
        });

        $paymentID = Give_Helper_Payment::create_simple_payment();

        give_update_payment_meta($paymentID, 'my-text-field', 'foobar');

        ob_start();
        do_action( 'give_payment_receipt_after', get_post( $paymentID ), [] );
        $output = ob_get_clean();

        $this->assertContains( 'foobar', $output, 'Donation confirmation does not have custom field value.' );
        $this->assertContains( 'My Text Field', $output, 'Donation confirmation does not have custom field value label.' );
    }

    public function testNotConfirmationHasEmptyDonationMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make( 'my-text-field' )
                    ->label( 'My Text Field' )
                    ->showInReceipt()
            );
        });

        $paymentID = Give_Helper_Payment::create_simple_payment();

        give_update_payment_meta( $paymentID, 'my-text-field', '' ); // NOTE: Empty value.

        ob_start();
        do_action( 'give_payment_receipt_after', get_post( $paymentID ), [] );
        $output = ob_get_clean();

        $this->assertNotContains( 'foobar', $output, 'Donation confirmation has empty custom field value.' );
        $this->assertNotContains( 'My Text Field', $output, 'Donation confirmation has empty custom field value label.' );
    }

    public function testNotConfirmationHasDonationMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make( 'my-text-field' )
                    ->label( 'My Text Field' )
            // NOTE: Not shown in receipt.
            );
        });

        $paymentID = Give_Helper_Payment::create_simple_payment();

        give_update_payment_meta( $paymentID, 'my-text-field', 'foobar' );

        ob_start();
        do_action( 'give_payment_receipt_after', get_post( $paymentID ), [] );
        $output = ob_get_clean();

        $this->assertNotContains( 'foobar', $output, 'Donation confirmation shows hidden custom field value.' );
        $this->assertNotContains( 'My Text Field', $output, 'Donation confirmation shows hidden custom field value label.' );
    }

    /*
    |--------------------------------------------------------------------------
    | Donor Meta
    |--------------------------------------------------------------------------
    */

    public function testConfirmationHasDonorMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make( 'my-text-field' )
                    ->label( 'My Text Field' )
                    ->showInReceipt()
                    ->storeAsDonorMeta()
            );
        });

        $paymentID = Give_Helper_Payment::create_simple_payment();

        $donorID = give_get_payment_meta( $paymentID, '_give_payment_donor_id' );
        Give()->donor_meta->update_meta( $donorID, 'my-text-field', 'foobar' );

        ob_start();
        do_action( 'give_payment_receipt_after', get_post( $paymentID ), [] );
        $output = ob_get_clean();

        $this->assertContains( 'foobar', $output, 'Donation confirmation does not have custom field donor meta.' );
        $this->assertContains( 'My Text Field', $output, 'Donation confirmation does not have custom field donor meta label.' );
    }

    public function testNotConfirmationHasEmptyDonorMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make( 'my-text-field' )
                    ->label( 'My Text Field' )
                    ->showInReceipt()
                    ->storeAsDonorMeta()
            );
        });

        $paymentID = Give_Helper_Payment::create_simple_payment();

        $donorID = give_get_payment_meta( $paymentID, '_give_payment_donor_id' );
        Give()->donor_meta->update_meta( $donorID, 'my-text-field', '' ); // NOTE: Empty value.

        ob_start();
        do_action( 'give_payment_receipt_after', get_post( $paymentID ), [] );
        $output = ob_get_clean();

        $this->assertNotContains( 'foobar', $output, 'Donation confirmation has empty custom field donor meta value.' );
        $this->assertNotContains( 'My Text Field', $output, 'Donation confirmation has empty custom field donor meta value label.' );
    }

    public function testNotConfirmationHasDonorMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make( 'my-text-field' )
                    ->label( 'My Text Field' )
                    ->storeAsDonorMeta()
            // NOTE: Not shown in receipt.
            );
        });

        $paymentID = Give_Helper_Payment::create_simple_payment();

        $donorID = give_get_payment_meta( $paymentID, '_give_payment_donor_id' );
        Give()->donor_meta->update_meta( $donorID, 'my-text-field', 'foobar' );

        ob_start();
        do_action( 'give_payment_receipt_after', get_post( $paymentID ), [] );
        $output = ob_get_clean();

        $this->assertNotContains( 'foobar', $output, 'Donation confirmation shows hidden custom field donor metea value.' );
        $this->assertNotContains( 'My Text Field', $output, 'Donation confirmation shows hidden custom field donor meta value label.' );
    }
}
