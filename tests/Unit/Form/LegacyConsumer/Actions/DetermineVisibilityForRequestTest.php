<?php

namespace Give\Tests\Unit\Form\LegacyConsumer\Actions;

use Give\Form\LegacyConsumer\Actions\DetermineVisibilityForRequest;
use Give\Framework\FieldsAPI\Text;
use Give_Helper_Form;
use Give\Tests\TestCase;

final class DetermineVisibilityForRequestTest extends TestCase
{
    public function testItDeterminesAFieldIsVisibleComparedToString()
    {
        $form = Give_Helper_Form::create_simple_form();
        $requestData = [
            'give-form-id' => $form->ID,
            'give-amount' => '10.00',
            'my_field' => 'test',
            'my_other_field' => 'test'
        ];

        $field = new Text( 'my_field' );
        $field->showIf('my_other_field', '=', 'test');

        $action = new DetermineVisibilityForRequest( $field, $requestData );

        $this->assertTrue( $action->__invoke() );
    }

    public function testItDeterminesAFieldIsNotVisibleComparedToString()
    {
        $form = Give_Helper_Form::create_simple_form();
        $requestData = [
            'give-form-id' => $form->ID,
            'give-amount' => '10.00',
            'my_field' => 'test',
            'my_other_field' => 'nottest'
        ];

        $field = new Text( 'my_field' );
        $field->showIf('my_other_field', '=', 'test');

        $action = new DetermineVisibilityForRequest( $field, $requestData );

        $this->assertFalse( $action->__invoke() );
    }

    public function testItDeterminesAFieldIsVisibleComparedToGiveAmount()
    {
        $form = Give_Helper_Form::create_simple_form();
        $requestData = [
            'give-form-id' => $form->ID,
            'give-amount' => '10.00',
            'my_field' => 'test',
        ];

        $field = new Text( 'my_field' );
        $field->showIf('give-amount', '<', '100');

        $action = new DetermineVisibilityForRequest( $field, $requestData );

        $this->assertTrue( $action->__invoke() );
    }

    public function testItDeterminesAFieldIsNotVisibleComparedToGiveAmount()
    {
        $form = Give_Helper_Form::create_simple_form();
        $requestData = [
            'give-form-id' => $form->ID,
            'give-amount' => '10.00',
            'my_field' => 'test',
        ];

        $field = new Text( 'my_field' );
        $field->showIf('give-amount', '>=', '100');

        $action = new DetermineVisibilityForRequest( $field, $requestData );

        $this->assertFalse( $action->__invoke() );
    }
}
