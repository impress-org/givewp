<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;

final class MoveNodeProxyTest extends TestCase {

    public function testMoveNodeAfter() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
            new FormField( 'text', 'my-second-text-field' ),
        ]);

        $collection->move( 'my-text-field' )->after( 'my-second-text-field' );

        $this->assertEquals( 1, $collection->getNodeIndexByName( 'my-text-field' ) );
        $this->assertEquals( 0, $collection->getNodeIndexByName( 'my-second-text-field' ) );
    }
}