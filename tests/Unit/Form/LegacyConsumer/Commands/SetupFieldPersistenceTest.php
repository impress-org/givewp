<?php

use Give\Framework\FieldsAPI\Text;

final class SetupFieldPersistenceTest extends \Give\Tests\TestCase
{

    public function testFieldPersistence()
    {
        add_action('give_fields_donation_form', function ($form) {
            $form->append(
                Text::make('my-text-field')
                    ->emailTag('myTextField')
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
