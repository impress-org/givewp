<?php

namespace Give\Campaigns\Blocks\CampaignComments\Controller;

use Give\Campaigns\Blocks\CampaignComments\DataTransferObjects\BlockAttributes;

/**
 * @since 4.0.0
 */
class BlockRenderController
{
    /**
     * @since 4.0.0
     */
    public function render(array $attributes, string $secondaryColor): string
    {
        $blockAttributes = BlockAttributes::fromArray($attributes);

        $encodedAttributes = json_encode($blockAttributes->toArray());

        $blockId = $blockAttributes->blockId;

        return "<div id='givewp-campaign-comments-block-{$blockId}' data-secondary-color='{$secondaryColor}' data-givewp-campaign-comments data-attributes='{$encodedAttributes}'></div>";
    }
}
