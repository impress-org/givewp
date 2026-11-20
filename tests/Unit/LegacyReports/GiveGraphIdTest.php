<?php

namespace Give\Tests\Unit\LegacyReports;

use Give\Tests\TestCase;
use Give_Graph;
use ReflectionProperty;

/**
 * @since TBD
 */
final class GiveGraphIdTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/reports/class-give-graph.php';
    }

    /**
     * The id is only a DOM id suffix, so only its shape and uniqueness matter.
     *
     * @since TBD
     */
    public function testEachGraphGetsAnMd5ShapedId(): void
    {
        $first = $this->graphId(new Give_Graph([]));
        $second = $this->graphId(new Give_Graph([]));

        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $first);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $second);
        $this->assertNotSame($first, $second);
    }

    /**
     * @since TBD
     */
    private function graphId(Give_Graph $graph): string
    {
        $property = new ReflectionProperty($graph, 'id');
        $property->setAccessible(true);

        return $property->getValue($graph);
    }
}
