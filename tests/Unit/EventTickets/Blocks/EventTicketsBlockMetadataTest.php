<?php

namespace Give\Tests\Unit\EventTickets\Blocks;

use Give\Tests\TestCase;

/**
 * @since TBD
 */
class EventTicketsBlockMetadataTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testBlockJsonIsValidAndUsesBlockApiVersion3(): void
    {
        $metadata = wp_json_file_decode(
            trailingslashit(GIVE_PLUGIN_DIR) . 'src/EventTickets/resources/blocks/EventTicketsBlock/block.json',
            ['associative' => true]
        );

        $this->assertIsArray($metadata);
        $this->assertSame(3, $metadata['apiVersion']);
        $this->assertSame('givewp/event-tickets', $metadata['name']);
        $this->assertSame(['type' => 'number'], $metadata['attributes']['eventId']);
    }
}
