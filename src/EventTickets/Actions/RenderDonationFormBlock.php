<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Fields\EventTickets;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;

class RenderDonationFormBlock
{
    /**
     * Renders the EventTickets field for the donation form block.
     *
     * @param Node|null $node The node instance.
     * @param BlockModel $block The block model instance.
     * @param int $blockIndex The index of the block.
     *
     * @return EventTickets The EventTickets field instance.
     * @throws EmptyNameException
     */
    public function __invoke(?Node $node, BlockModel $block, int $blockIndex, int $formId): ?Node
    {
        if ( ! $block->getAttribute('eventId')) {
            return $node;
        }

        return (give(ConvertEventTicketsBlockToFieldsApi::class))($block, $formId);
    }
}
