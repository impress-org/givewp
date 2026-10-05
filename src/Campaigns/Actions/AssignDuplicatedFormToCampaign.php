<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Repositories\CampaignRepository;
use Give\Log\Log;

/**
 * @since 4.0.0
 */
class AssignDuplicatedFormToCampaign
{
    /**
     * @since 4.0.0
     * @var CampaignRepository
     */
    protected $campaignRepository;

    /**
     * @since 4.0.0
     */
    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @since TBD Leave the copy of a form with no campaign standalone instead of logging an error.
     * @since 4.0.0
     */
    public function __invoke($duplicatedFormID, $originalFormID)
    {
        $campaign = $this->campaignRepository->queryByFormId($originalFormID)->get();

        if ( ! $campaign) {
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
