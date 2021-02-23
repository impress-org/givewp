<?php

use PHPUnit\Framework\TestCase;
use Give\Form\LegacyConsumer\FieldView;
use Give\Framework\FieldsAPI\FormField;

final class FieldViewTest extends TestCase {

    public function testSupportsAttributes() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->setAttributes([
            'foo' => 'bar',
            'baz' => 'qux quux',
        ]);

        ob_start();
        FieldView::render( $field );
        $output = ob_get_clean();

        $this->assertContains( "foo=\"bar\"", $output );
        $this->assertContains( "baz=\"qux quux\"", $output );
    }
}
