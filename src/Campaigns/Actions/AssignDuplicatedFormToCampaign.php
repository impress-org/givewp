<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Repositories\CampaignRepository;
use Give\Log\Log;

class AssignDuplicatedFormToCampaign
{
    /**
     * @var CampaignRepository
     */
    protected $campaignRepository;

    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    public function __invoke($duplicatedFormID, $originalFormID)
    {
        $campaign = $this->campaignRepository->queryByFormId($originalFormID)->get();

        try {
            $this->campaignRepository->addCampaignForm($campaign, $duplicatedFormID, true);
        } catch (\Exception $e) {
            Log::error('Failed to assign duplicated form to campaign.', [
                'campaign_id' => $campaign->id,
                'duplicated_form_id' => $duplicatedFormID,
                'original_form_id' => $originalFormID,
                'error' => $e->getMessage(),
            ]);
            return;
        }
    }
}
