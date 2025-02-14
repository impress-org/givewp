<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @unreleased
 */
class ConvertQueryDataToCampaign
{
    /**
     * @unreleased
     */
    public function __invoke(object $queryObject): Campaign
    {
        return new Campaign([
            'id' => (int)$queryObject->id,
            'defaultFormId' => (int)$queryObject->defaultFormId,
            'type' => new CampaignType($queryObject->type),
            'enableCampaignPage' => (bool)$queryObject->enableCampaignPage,
            'title' => $queryObject->title,
            'shortDescription' => $queryObject->shortDescription,
            'longDescription' => $queryObject->longDescription,
            'logo' => $queryObject->logo,
            'image' => $queryObject->image,
            'primaryColor' => $queryObject->primaryColor,
            'secondaryColor' => $queryObject->secondaryColor,
            'goal' => (int)$queryObject->goal,
            'goalType' => new CampaignGoalType($queryObject->goalType),
            'startDate' => $queryObject->startDate ? Temporal::toDateTime($queryObject->startDate) : Temporal::toDateTime('0000-00-00 00:00:00'),
            'endDate' => $queryObject->endDate ? Temporal::toDateTime($queryObject->endDate) : Temporal::toDateTime('0000-00-00 00:00:00'),
            'status' => new CampaignStatus($queryObject->status),
            'createdAt' => Temporal::toDateTime($queryObject->createdAt),
        ]);
    }
}
