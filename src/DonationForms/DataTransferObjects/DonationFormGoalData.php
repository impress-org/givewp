<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\DonationForms\DonationQuery;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\SubscriptionQuery;
use Give\DonationForms\ValueObjects\GoalProgressType;
use Give\DonationForms\ValueObjects\GoalSource;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @since 4.1.0 added goalSource
 * @since 3.0.0
 */
class DonationFormGoalData implements Arrayable
{
    /**
     * @var int
     */
    public $formId;
    /**
     * @var FormSettings
     */
    public $formSettings;
    /**
     * @var false
     */
    public $isEnabled;
    /**
     * @var GoalType
     */
    public $goalType;
    /**
     * @var int
     */
    public $targetAmount;
    /**
     * @var GoalProgressType
     */
    public $goalProgressType;
    /**
     * @var string|null
     */
    public $goalStartDate;
    /**
     * @var string|null
     */
    public $goalEndDate;
    /**
     * @since 4.1.0
     *
     * @var Campaign|null
     */
    public $campaign;
    /**
     * @var GoalSource $goalSource
     */
    public $goalSource;

    /**
     * @since 3.0.0
     */
    public function __construct(int $formId, FormSettings $formSettings)
    {
        $this->formId = $formId;
        $this->formSettings = $formSettings;
        $this->isEnabled = $formSettings->enableDonationGoal ?? false;
        $this->goalType = $formSettings->goalType ?? GoalType::AMOUNT();
        $this->goalSource = $formSettings->goalSource ?? GoalSource::CAMPAIGN();
        $this->targetAmount = $this->formSettings->goalAmount ?? 0;
        $this->goalProgressType = $this->formSettings->goalProgressType ?? GoalProgressType::ALL_TIME();
        $this->goalStartDate = $this->formSettings->goalStartDate ?? null;
        $this->goalEndDate = $this->formSettings->goalEndDate ?? null;
        $this->campaign = Campaign::findByFormId($this->formId);
    }

    /**
     * @since 4.1.0 switch between Campaign goal and Form goal
     * @since      3.0.0
     *
     * @return int|float
     */
    public function getCurrentAmount()
    {
        switch ($this->getGoalType()->getValue()):
            case 'donors':
            case 'donorsFromSubscriptions':
                return $this->getQuery()->countDonors();
            case 'donations':
                return $this->goalSource->isCampaign()
                    ? $this->getQuery()->countDonations()
                    : $this->getQuery()->count();
            case 'subscriptions':
                return $this->getQuery()->count();
            case 'amountFromSubscriptions':
                return $this->getQuery()->sumInitialAmount();
            default:
                return $this->getQuery()->sumIntendedAmount();
        endswitch;
    }

    /**
     * Check if goal type is subscription
     *
     * @since 4.1.0
     */
    public function isSubscription(): bool
    {
        return in_array(
            $this->getGoalType()->getValue(), [
            'subscriptions',
            'amountFromSubscriptions',
            'donorsFromSubscriptions',
        ], true);
    }

    /**
     * @since 4.1.0
     */
    public function getQuery()
    {
        return $this->goalSource->isCampaign()
            ? $this->getCampaignQuery()
            : $this->getFormQuery();
    }

    /**
     * Get Campaign query
     *
     * @since 4.1.0
     *
     * @return CampaignDonationQuery|SubscriptionQuery
     */
    private function getCampaignQuery()
    {
        if ($this->isSubscription()) {
            $query = new SubscriptionQuery();

            $ids = array_map(function ($form) {
                return $form->id;
            }, $this->campaign->forms()->getAll());

            $query->forms($ids);

            return $query;
        }

        return new CampaignDonationQuery($this->campaign);
    }

    /**
     * Get Form query
     *
     * @since 4.7.0 add support for date range
     * @since 4.1.0
     *
     * @return DonationQuery|SubscriptionQuery
     */
    private function getFormQuery()
    {
        $query = $this->isSubscription()
            ? new SubscriptionQuery()
            : new DonationQuery();

        if ($this->goalStartDate && $this->goalEndDate) {
            $query->between($this->goalStartDate, $this->goalEndDate);
        }

        if ($this->goalStartDate && !$this->goalEndDate) {
            $query->between($this->goalStartDate, current_datetime()->format('Y-m-d'));
        }

        $query->form($this->formId);

        return $query;
    }

    /**
     * Get goal type
     *
     * @return CampaignGoalType|GoalType
     */
    public function getGoalType()
    {
        return $this->goalSource->isCampaign()
            ? $this->campaign->goalType
            : $this->goalType;
    }

    /**
     * Get target amount
     *
     * @since 4.1.0
     *
     * @return float|int
     */
    public function getTargetAmount()
    {
        return $this->goalSource->isCampaign()
            ? $this->campaign->goal ?? 0
            : $this->targetAmount;
    }

    /**
     * Check if goal type is an amount
     *
     * @since 4.1.0
     */
    public function isAmount(): bool
    {
        return $this->getGoalType()->isOneOf(
            $this->getGoalType()::AMOUNT(),
            $this->getGoalType()::AMOUNT_FROM_SUBSCRIPTIONS()
        );
    }

    /**
     * @since 3.0.0
     */
    public function getLabel(): string
    {
        if ($this->getGoalType()->isDonors() || $this->getGoalType()->isDonorsFromSubscriptions()) {
            return __('donors', 'give');
        }

        if ($this->getGoalType()->isDonations()) {
            return __('donations', 'give');
        }

        if ($this->getGoalType()->isSubscriptions()) {
            return __('recurring donations', 'give');
        }

        return __('amount', 'give');
    }

    /**
     * @since 4.2.0 add percentage value
     * @since 3.0.0
     */
    public function toArray(): array
    {
        $currentAmount = $this->getCurrentAmount();
        $targetAmount = $this->getTargetAmount();
        $goalTypeIsAmount = $this->isAmount();

        $progressPercentage = ! $currentAmount || ! $targetAmount ? 0 : ($currentAmount / $targetAmount) * 100;

        return [
            'type' => $this->getGoalType()->getValue(),
            'typeIsCount' => ! $goalTypeIsAmount,
            'typeIsMoney' => $goalTypeIsAmount,
            'enabled' => $this->isEnabled,
            'show' => $this->isEnabled,
            'currentAmount' => $currentAmount,
            'targetAmount' => $targetAmount,
            'label' => $this->getLabel(),
            'percentage' => $progressPercentage,
            'isAchieved' => $this->isEnabled && $this->formSettings->enableAutoClose && $progressPercentage >= 100,
        ];
    }

    /**
     * Get total donation revenue, the exception is for subscription amount goal, it will return the sum of initial amount
     *
     * @since 4.3.0
     */
    public function getTotalDonationRevenue()
    {
        if ($this->getGoalType()->getValue() === 'amountFromSubscriptions') {
            $query = $this->getQuery();

            return $query->sumInitialAmount();
        }


        $query = $this->goalSource->isCampaign()
            ? new CampaignDonationQuery($this->campaign)
            : (new DonationQuery())->form($this->formId);

        return $query->sumIntendedAmount();
    }
}
