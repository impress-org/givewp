<?php

namespace Give\Campaigns\Factories;

use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @unreleased
 */
class CampaignFactory extends ModelFactory
{
    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        $currentDate = Temporal::getCurrentDateTime();

        return [
            'type' => CampaignType::CORE(),
            'enableCampaignPage' => true,
            'title' => __('GiveWP Campaign', 'give'),
            'shortDescription' => __('Campaign short description', 'give'),
            'longDescription' => __('Campaign long description', 'give'),
            'goal' => 10000000,
            'goalType' => CampaignGoalType::AMOUNT(),
            'status' => CampaignStatus::ACTIVE(),
            'logo' => '',
            'image' => '',
            'primaryColor' => '#28C77B',
            'secondaryColor' => '#FFA200',
            'createdAt' => Temporal::withoutMicroseconds($currentDate),
            'startDate' => Temporal::withoutMicroseconds($currentDate),
            'endDate' => Temporal::withoutMicroseconds($currentDate->modify('+1 day')),
        ];
    }
}
