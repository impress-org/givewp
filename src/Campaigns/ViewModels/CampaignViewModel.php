<?php

namespace Give\Campaigns\ViewModels;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @unreleased
 */
class CampaignViewModel
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
    public function exports(): array
    {
        return [
            'id' => $this->campaign->id,
            'pageId' => $this->campaign->pageId,
            'pagePermalink' => $this->campaign->pageId
                ? get_permalink($this->campaign->pageId)
                : null,
            'enableCampaignPage' => $this->campaign->enableCampaignPage,
            'defaultFormId' => $this->campaign->defaultFormId,
            'defaultFormTitle' => $this->campaign->defaultForm()->title,
            'type' => $this->campaign->type->getValue(),
            'title' => $this->campaign->title,
            'shortDescription' => $this->campaign->shortDescription,
            'longDescription' => $this->campaign->longDescription,
            'logo' => $this->campaign->logo,
            'image' => $this->campaign->image,
            'primaryColor' => $this->campaign->primaryColor,
            'secondaryColor' => $this->campaign->secondaryColor,
            'goal' => $this->campaign->goal,
            'goalType' => $this->campaign->goalType->getValue(),
            'goalStats' => $this->campaign->getGoalStats(),
            'status' => $this->campaign->status->getValue(),
            'startDate' => Temporal::getFormattedDateTime($this->campaign->startDate),
            'endDate' => Temporal::getFormattedDateTime($this->campaign->endDate),
            'createdAt' => Temporal::getFormattedDateTime($this->campaign->createdAt),
        ];
    }
}
