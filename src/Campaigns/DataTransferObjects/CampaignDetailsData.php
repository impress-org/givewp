<?php

namespace Give\Campaigns\DataTransferObjects;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @unreleased
 */
class CampaignDetailsData implements Arrayable
{
    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @unreleased
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return array_merge(
            $this->campaign->toArray(),
            [
                'defaultFormId' => $this->campaign->defaultForm()->id,
            ]
        );
    }
}
