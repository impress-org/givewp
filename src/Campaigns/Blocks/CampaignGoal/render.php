<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (
    ! isset($attributes['campaignId'])
    || ! $campaign = give(CampaignRepository::class)->getById($attributes['campaignId'])
) {
    return;
}

$stats = $campaign->getGoalStats();

$getGoalDescription = function(CampaignGoalType $goalType) {
    $data = [
        'amount' => __('Amount raised', 'give'),
        'donations' => __('Number of donations', 'give'),
        'donors' => __('Number of donors', 'give'),
        'amountFromSubscriptions' => __('Recurring amount raised', 'give'),
        'subscriptions' => __('Number of recurring donations', 'give'),
        'donorsFromSubscriptions' => __('Number of recurring donors', 'give'),
    ];

    return $data[$goalType->getvalue()];
};

$getGoalFormattedValue = function($goalType, $value) {
    switch ($goalType) {
        case 'amount':
        case 'amountFromSubscriptions':
            $amount = Money::fromDecimal($value, give_get_currency());
            return $amount->formatToLocale();
        default:
            return $value;
    }
};

?>

<div class="give-campaign-goal">
    <div class="give-campaign-goal__container">
        <div class="give-campaign-goal__container-item">
            <span><?= $getGoalDescription($campaign->goalType); ?></span>
            <strong>
                <?= $getGoalFormattedValue($campaign->goalType, $stats['actual']); ?>
            </strong>
        </div>
        <div class="give-campaign-goal__container-item">
            <span><?= esc_html__('Our goal', 'give'); ?></span>
            <strong><?= $getGoalFormattedValue($campaign->goalType, $campaign->goal); ?></strong>
        </div>
    </div>
    <div class="give-campaign-goal__progress-bar">
        <div class="give-campaign-goal__progress-bar-container">
            <div class="give-campaign-goal__progress-bar-progress"
                 style="width: <?= $stats['percentage']; ?>%">
            </div>
        </div>
    </div>
</div>

