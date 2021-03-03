<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Form\LegacyConsumer\Commands\SetupFieldEmailTag;

final class SetupFieldEmailTagTest extends Give_Unit_Test_Case {

    public function setUp() {
        parent::setUp();
        $this->paymentID = Give_Helper_Payment::create_simple_payment();
    }

    public function testEmailTagResolverDonationMeta() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->emailTag( 'myTextField' );

        give_update_payment_meta( $this->paymentID, 'my-text-field', 'foobar' );

        (new SetupFieldEmailTag( null ))->register( $field );

        $content = give_do_email_tags( '{myTextField}', [ 'payment_id' => $this->paymentID ] );
        $this->assertEquals( 'foobar', $content );
    }

    public function testEmailTagResolverDonorMeta() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->storeAsDonorMeta();
        $field->emailTag( 'myTextField' );

        $donorID = give_get_payment_meta( $this->paymentID, '_give_payment_donor_id' );
        Give()->donor_meta->update_meta( $donorID, $field->getName(), 'foobar' );

        (new SetupFieldEmailTag( null ))->register( $field );

        $content = give_do_email_tags( '{myTextField}', [ 'payment_id' => $this->paymentID ] );
        $this->assertEquals( 'foobar', $content );
    }
}
