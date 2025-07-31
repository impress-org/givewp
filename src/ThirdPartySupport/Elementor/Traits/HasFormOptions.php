<?php

namespace Give\ThirdPartySupport\Elementor\Traits;

use Exception;
use Give\Framework\Database\DB;

/**
 * Trait to get form options with campaigns
 *
 * @unreleased
 */
trait HasFormOptions
{
    /**
     * Get form options with campaigns
     *
     * @unreleased
     *
     * @return array
     */
    public function getFormOptionsWithCampaigns(): array
    {
        $campaignsWithForms = $this->getCampaignsWithForms();

        if (empty($campaignsWithForms)) {
            return [];
        }

        $campaignOptions = [];
        $formOptionsGroup = [];
        $campaignGroups = [];

        // Group forms by campaign
        foreach ($campaignsWithForms as $item) {
            // Skip items without campaign association
            if (empty($item->campaign_id) || empty($item->campaign_title)) {
                continue;
            }

            $campaignId = $item->campaign_id;
            $campaignTitle = $item->campaign_title;

            // Add to campaign options if not already added
            if (!isset($campaignOptions[$campaignId])) {
                $campaignOptions[$campaignId] = $campaignTitle;
                $campaignGroups[$campaignId] = [
                    'label' => $campaignTitle,
                    'options' => []
                ];
            }

            // Add form to the campaign group
            $campaignGroups[$campaignId]['options'][$item->id] = $item->title;
        }

        $formOptionsGroup = array_values($campaignGroups);

        return $formOptionsGroup;
    }

    /**
     * Query campaigns with forms
     *
     * @unreleased
     *
     * @return array
     */
    public function getCampaignsWithForms()
    {
        try {
            $query = DB::table('posts', 'forms')
                ->select(
                    ['forms.ID', 'id'],
                    ['forms.post_title', 'title'],
                    ['campaigns.campaign_title', 'campaign_title'],
                    ['campaigns.id', 'campaign_id']
                )
                ->innerJoin('give_campaign_forms', 'forms.ID', 'campaign_forms.form_id', 'campaign_forms')
                ->innerJoin('give_campaigns', 'campaign_forms.campaign_id', 'campaigns.id', 'campaigns')
                ->where('forms.post_status', 'publish');

            return $query->getAll();
        } catch (Exception $e) {
            error_log('getCampaignsWithForms error: ' . $e->getMessage());
            return [];
        }
    }
}
