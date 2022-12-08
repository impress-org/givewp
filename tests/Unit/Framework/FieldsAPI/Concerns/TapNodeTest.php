<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\TapNode;
use PHPUnit\Framework\TestCase;

final class TapNodeTest extends TestCase
{

    public function testTappedNodeReturnsOriginalNode()
    {
        $node = new class {
            use TapNode;
        };

        $this->assertEquals($node, $node->tap(function ($tappedNode) {
            // This section intentionally left blank.
        }));
    }

    public function testTappedNodeIsUpdated()
    {
        $node = new class {
            use TapNode;

            public $updated = false;
        };

        $this->assertTrue($node->tap(function ($tappedNode) {
            $tappedNode->updated = true;
        })->updated);
    }
}
