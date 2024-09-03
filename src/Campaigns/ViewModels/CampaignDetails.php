<?php

namespace Give\Campaigns\ViewModels;

use Give\Campaigns\Models\Campaign;

/**
 * @unreleased
 */
class CampaignDetails
{
    /**
     * @unreleased
     *
     * @var Campaign
     */
    protected $campaign;

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
    public function exports(): array
    {
        return $this->campaign->toArray();
    }
}
