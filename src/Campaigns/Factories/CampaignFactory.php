<?php

namespace Give\Campaigns\Factories;

use Exception;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 4.0.0
 */
class CampaignFactory extends ModelFactory
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function definition(): array
    {
        $currentDate = Temporal::getCurrentDateTime();
        $createdAt = Temporal::withoutMicroseconds($currentDate);

        return [
            'type' => CampaignType::CORE(),
            'title' => __('GiveWP Campaign', 'give'),
            'shortDescription' => __('Campaign short description', 'give'),
            'longDescription' => __('Campaign long description', 'give'),
            'goal' => 5000,
            'goalType' => CampaignGoalType::AMOUNT(),
            'status' => CampaignStatus::ACTIVE(),
            'logo' => '',
            'image' => '',
            'primaryColor' => '#28C77B',
            'secondaryColor' => '#FFA200',
            'createdAt' => $createdAt,
            'startDate' => $createdAt,
            'endDate' => null,
        ];
    }
}
