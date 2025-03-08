<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Repositories\CampaignRepository;
use Give\Log\Log;

/**
 * @unreleased
 */
class AssignDuplicatedFormToCampaign
{
    /**
     * @unreleased
     * @var CampaignRepository
     */
    protected $campaignRepository;

    /**
     * @unreleased
     */
    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @unreleased
     */
    public function __invoke($duplicatedFormID, $originalFormID)
    {
        $campaign = $this->campaignRepository->queryByFormId($originalFormID)->get();

        if(!$campaign) {
            Log::error('Campaign does not exist for duplicated form.', [
                'duplicated_form_id' => $duplicatedFormID,
                'original_form_id' => $originalFormID,
            ]);
            return;
        }

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
