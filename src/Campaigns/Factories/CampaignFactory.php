<?php

namespace Give\Campaigns\Factories;

use DateTime;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Models\Factories\ModelFactory;

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
        return [
            'pageId' => 1,
            'type' => CampaignType::CORE(),
            'title' => __('GiveWP Campaign', 'give'),
            'shortDescription' => __('Campaign short description', 'give'),
            'longDescription' => __('Campaign long description', 'give'),
            'goal' => 10000000,
            'status' => CampaignStatus::ACTIVE(),
            'logo' => '',
            'image' => '',
            'primaryColor' => '#28C77B',
            'secondaryColor' => '#FFA200',
            'startDate' => new DateTime(),
            'endDate' => (new DateTime())->modify('+1 day'),
            'createdAt' => new DateTime(),
        ];
    }
}
