<?php

use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Facades\Factory;
use Give\Framework\FieldsAPI\Text;

class FactoryTest extends Give_Unit_Test_Case {

	/**
	 * @unreleased
	 */
	public function testReturnExceptionWhenMakeFieldWithEmptyName() {
		$this->expectException( EmptyNameException::class );
		Factory::make( 'text', '' );
	}

	/**
	 * @unreleased
	 */
	public function testReturnExceptionWhenMakeFieldWithNullName() {
		$this->expectException( EmptyNameException::class );
		Factory::make( 'text', null );
	}

	/**
	 * @unreleased
	 */
	public function testReturnExceptionWhenMakeFieldWithEmptyNameWithMakeFunction() {
		$this->expectException( EmptyNameException::class );
		Text::make( '' );
	}

	/**
	 * @unreleased
	 */
	public function testReturnExceptionWhenMakeFieldWithNullNameWithMakeFunction() {
		$this->expectException( EmptyNameException::class );
		Text::make( null );
	}

	public function testMakeTextField() {
		$field = Factory::make( 'text', 'my-text-field' );

		$this->assertInstanceOf( Text::class, $field );
		$this->assertEquals( 'my-text-field', $field->getName() );
	}
}
