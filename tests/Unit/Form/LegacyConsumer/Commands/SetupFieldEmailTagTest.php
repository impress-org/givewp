<?php

namespace Give\Tests\Unit\Form\LegacyConsumer\Commands;

use Give\Form\LegacyConsumer\Commands\SetupFieldEmailTag;
use Give\Framework\FieldsAPI\Text;
use Give_Helper_Payment;
use Give\Tests\TestCase;

final class SetupFieldEmailTagTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->paymentID = Give_Helper_Payment::create_simple_payment();
    }

    public function testEmailTagResolverDonationMeta()
    {
        $field = Text::make('my-text-field')->emailTag('myTextField');

        give_update_payment_meta($this->paymentID, 'my-text-field', 'foobar');

        (new SetupFieldEmailTag(null))->register($field);

        $content = give_do_email_tags('{myTextField}', ['payment_id' => $this->paymentID]);
        $this->assertEquals('foobar', $content);
    }

    public function testEmailTagResolverDonorMeta()
    {
        $field = Text::make( 'my-text-field' )
	        ->storeAsDonorMeta()
			->emailTag( 'myTextField' );

        $donorID = give_get_payment_meta( $this->paymentID, '_give_payment_donor_id' );
        Give()->donor_meta->update_meta( $donorID, $field->getName(), 'foobar' );

        (new SetupFieldEmailTag( null ))->register( $field );

        $content = give_do_email_tags( '{myTextField}', [ 'payment_id' => $this->paymentID ] );
        $this->assertEquals( 'foobar', $content );
    }
}
