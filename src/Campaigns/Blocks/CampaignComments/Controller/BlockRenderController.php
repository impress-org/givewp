<?php

namespace Give\Campaigns\Blocks\CampaignComments\Controller;

use Give\Campaigns\Blocks\CampaignComments\DataTransferObjects\BlockAttributes;

/**
 * @unreleased
 */
class BlockRenderController
{
    /**
     * @unreleased
     */
    public function render(array $attributes): string
    {
        $blockAttributes = BlockAttributes::fromArray($attributes);

        $encodedAttributes = json_encode($blockAttributes->toArray());

        $blockId = $blockAttributes->blockId;

        return "<div id='givewp-campaign-comments-block-{$blockId}' data-givewp-campaign-comments data-attributes='{$encodedAttributes}'></div>";
    }
}
