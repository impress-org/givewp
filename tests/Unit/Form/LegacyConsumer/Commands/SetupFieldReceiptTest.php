<?php

namespace Give\Tests\Unit\Form\LegacyConsumer\Commands;

use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Text;
use Give\Receipt\DonationReceipt;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

final class SetupFieldReceiptTest extends TestCase
{
    use RefreshDatabase;
    /*
    |--------------------------------------------------------------------------
    | Donation Meta
    |--------------------------------------------------------------------------
    */

    public function testReceiptHasDonationMeta()
    {
        add_action('give_fields_donation_form', function ($form) {
            $form->append(
                Text::make('textField')
                    ->label('Text Field')
                    ->showInReceipt()
            );
        });

        $paymentID = Donation::factory()->create()->id;

        give_update_payment_meta($paymentID, 'textField', 'foobar');

        $receipt = new DonationReceipt( $paymentID );
        do_action( 'give_new_receipt', $receipt );

        $section = $receipt[ DonationReceipt::ADDITIONALINFORMATIONSECTIONID ];

        $this->assertArrayHasKey( 'textField', $section->getLineItems() );
    }

    public function testNotReceiptHasEmptyDonationMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make('my-text-field')
                    ->label( 'My Text Field' )
                    ->showInReceipt()
            );
        });

        $paymentID = Donation::factory()->create()->id;

        give_update_payment_meta( $paymentID, 'my-text-field', '' ); // NOTE: Empty value.

        $receipt = new DonationReceipt( $paymentID );
        do_action( 'give_new_receipt', $receipt );

        $section = $receipt[ DonationReceipt::ADDITIONALINFORMATIONSECTIONID ];

        $this->assertArrayNotHasKey( 'my-text-field', $section->getLineItems() );
    }

    public function testNotReceiptHasDonationMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make('my-text-field')
                    ->label( 'My Text Field' )
            // NOTE: Not shown in receipt.
            );
        });

        $paymentID = Donation::factory()->create()->id;

        give_update_payment_meta( $paymentID, 'my-text-field', 'foobar' );

        $receipt = new DonationReceipt( $paymentID );
        do_action( 'give_new_receipt', $receipt );

        $section = $receipt[ DonationReceipt::ADDITIONALINFORMATIONSECTIONID ];

        $this->assertArrayNotHasKey( 'my-text-field', $section->getLineItems() );
    }

    /*
    |--------------------------------------------------------------------------
    | Donor Meta
    |--------------------------------------------------------------------------
    */

    public function testReceiptHasDonorMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make('my-text-field')
                    ->label( 'My Text Field' )
                    ->showInReceipt()
                    ->storeAsDonorMeta()
            );
        });

        $paymentID = Donation::factory()->create()->id;

        $donorID = give_get_payment_meta( $paymentID, '_give_payment_donor_id' );
        Give()->donor_meta->update_meta( $donorID, 'my-text-field', 'foobar' );

        $receipt = new DonationReceipt( $paymentID );
        do_action( 'give_new_receipt', $receipt );

        $section = $receipt[ DonationReceipt::DONORSECTIONID ];

        $this->assertArrayHasKey( 'my-text-field', $section->getLineItems() );
    }

    public function testNotReceiptHasEmptyDonorMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make( 'my-text-field' )
                    ->label( 'My Text Field' )
                    ->showInReceipt()
                    ->storeAsDonorMeta()
            );
        });

        $paymentID = Donation::factory()->create()->id;

        $donorID = give_get_payment_meta( $paymentID, '_give_payment_donor_id' );
        Give()->donor_meta->update_meta( $donorID, 'my-text-field', '' ); // NOTE: Empty value.

        $receipt = new DonationReceipt( $paymentID );
        do_action( 'give_new_receipt', $receipt );

        $section = $receipt[ DonationReceipt::DONORSECTIONID ];

        $this->assertArrayNotHasKey( 'my-text-field', $section->getLineItems() );
    }

    public function testNotReceiptHasDonorMeta() {
        add_action( 'give_fields_donation_form', function( $form ) {
            $form->append(
                Text::make( 'my-text-field' )
                    ->label( 'My Text Field' )
                    ->storeAsDonorMeta()
            // NOTE: Not shown in receipt.
            );
        });

        $donation = Donation::factory()->create();

        $donorID = give_get_payment_meta( $donation->id, '_give_payment_donor_id' );
        Give()->donor_meta->update_meta( $donorID, 'my-text-field', 'foobar' );

        $receipt = new DonationReceipt( $donation->id );
        do_action( 'give_new_receipt', $receipt );

        $section = $receipt[ DonationReceipt::DONORSECTIONID ];

        $this->assertArrayNotHasKey( 'my-text-field', $section->getLineItems() );
    }
}
