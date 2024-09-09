<?php

namespace Give\Campaigns\ViewModels;

use Give\Campaigns\Models\Campaign;

/**
 * @unreleased
 */
class CampaignDetailsPage
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
        return [
            'overviewTab' => $this->campaign->toArray(),
            'settingsTab' => [
                'landingPageUrl' => admin_url('?action=edit_campaign_page&campaign_id=' . $this->campaign->id),
            ],
            'reportTab' => [],
            'updatesTab' => [],
        ];
    }
}
