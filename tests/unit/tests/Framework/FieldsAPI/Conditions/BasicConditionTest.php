<?php

namespace unit\tests\Framework\FieldsAPI\Conditions;

use Give\Framework\FieldsAPI\Conditions\BasicCondition;
use Give_Unit_Test_Case;

class BasicConditionTest extends Give_Unit_Test_Case {

    public function testConditionAcceptsEncodedOperator() {
        $condition = new BasicCondition('foo', '&lt;', 'bar');
        $this->assertTrue(true);
    }
}
