<?php

namespace Give\ThirdPartySupport\Elementor\Traits;

use Exception;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Log\Log;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;

/**
 * Trait to get campaign options for Elementor widgets
 *
 * @since 4.7.0
 */
trait HasCampaignOptions
{
    /**
     * Get the default campaign option
     *
     * @since 4.7.0
     */
    public function getDefaultCampaignOption(array $options): string
    {
        $default = !empty($options) ? array_key_first($options) : '';


        $campaignId = get_post_meta(get_the_ID(), CampaignPageMetaKeys::CAMPAIGN_ID, true);

        if (!$campaignId) {
            return $default;
        }

        return array_key_exists($campaignId, $options) ? $campaignId : $default;
    }
    /**
     * Get campaign options for select dropdown
     *
     * @since 4.7.0
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
