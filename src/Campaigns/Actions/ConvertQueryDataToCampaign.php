<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 4.0.0
 */
class ConvertQueryDataToCampaign
{
    /**
     * @since 4.0.0
     */
    public function __invoke(object $queryObject): Campaign
    {
        return new Campaign([
            'id' => (int)$queryObject->id,
            'pageId' => $queryObject->pageId ? (int)$queryObject->pageId : null,
            'defaultFormId' => $queryObject->defaultFormId ? (int)$queryObject->defaultFormId : null,
            'type' => new CampaignType($queryObject->type),
            'title' => $queryObject->title,
            'shortDescription' => $queryObject->shortDescription,
            'longDescription' => $queryObject->longDescription,
            'logo' => $queryObject->logo,
            'image' => $queryObject->image,
            'primaryColor' => $queryObject->primaryColor,
            'secondaryColor' => $queryObject->secondaryColor,
            'goal' => (int)$queryObject->goal,
            'goalType' => new CampaignGoalType($queryObject->goalType),
            'startDate' => $queryObject->startDate ? Temporal::toDateTime($queryObject->startDate) : null,
            'endDate' => $queryObject->endDate ? Temporal::toDateTime($queryObject->endDate) : null,
            'status' => new CampaignStatus($queryObject->status),
            'createdAt' => Temporal::toDateTime($queryObject->createdAt),
        ]);
    }
}
