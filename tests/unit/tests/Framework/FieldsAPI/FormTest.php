<?php

use Give\Framework\FieldsAPI\Form;
use PHPUnit\Framework\TestCase;

final class FormTest extends TestCase {

	public function testTypeIsForm() {
		$form = new Form();

		$this->assertSame('form', Form::TYPE);
		$this->assertSame('form', $form->getType());
	}

	public function testNameIsRoot() {
		$form = new Form();

		$this->assertSame('root', $form->getName());
	}
}
