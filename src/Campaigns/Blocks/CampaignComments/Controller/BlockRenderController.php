<?php

namespace Give\Campaigns\Blocks\CampaignComments\Controller;

use Give\Campaigns\Blocks\CampaignComments\DataTransferObjects\BlockAttributes;

/**
 * @since 4.0.0
 */
class BlockRenderController
{
    /**
     * @since TBD escape attribute values in block markup
     * @since 4.0.0
     */
    public function render(array $attributes, string $secondaryColor): string
    {
        $blockAttributes = BlockAttributes::fromArray($attributes);

        return sprintf(
            "<div id='givewp-campaign-comments-block-%s' data-secondary-color='%s' data-givewp-campaign-comments data-attributes='%s'></div>",
            esc_attr((string) $blockAttributes->blockId),
            esc_attr($secondaryColor),
            esc_attr((string) json_encode($blockAttributes->toArray()))
        );
    }
}
