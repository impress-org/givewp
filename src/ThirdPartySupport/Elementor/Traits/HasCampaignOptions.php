<?php

namespace Give\ThirdPartySupport\Elementor\Traits;

use Exception;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Log\Log;

/**
 * Trait to get campaign options for Elementor widgets
 *
 * @unreleased
 */
trait HasCampaignOptions
{
    /**
     * Get campaign options for select dropdown
     *
     * @unreleased
     */
    public function getCampaignOptions(): array
    {
        try {
            $campaignRepository = give(CampaignRepository::class);
            $campaigns = $campaignRepository->prepareQuery()
                ->where('status', 'active')
                ->orderBy('campaign_title', 'ASC')
                ->getAll();

            if (empty($campaigns)) {
                return [];
            }

            $options = [];
            foreach ($campaigns as $campaign) {
                $options[$campaign->id] = $campaign->title;
            }

            return $options;
        } catch (Exception $e) {
            Log::error('Elementor Campaign Options Error: ' . $e->getMessage());

            return [];
        }
    }
}
