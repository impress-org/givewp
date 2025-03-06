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
 * @since 3.0.0
 */
class DonationFormGoalData implements Arrayable
{
    /**
     * @var int
     */
    public $formId;
    /**
     * @var array
     */
    public $formSettings;
    /**
     * @var false
     */
    public $isEnabled;
    /**
     * @unreleased
     *
     * @var GoalSource
     */
    public $goalSource;
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
     * @unreleased
     *
     * @var Campaign|null
     */
    public $campaign;

    /**
     * @since 3.0.0
     */
    public function __construct(int $formId, FormSettings $formSettings)
    {
        $this->formId = $formId;
        $this->formSettings = $formSettings;
        $this->isEnabled = $formSettings->enableDonationGoal ?? false;
        $this->goalSource = $formSettings->goalSource ?? GoalSource::CAMPAIGN();
        $this->goalType = $formSettings->goalType ?? GoalType::AMOUNT();
        $this->targetAmount = $this->formSettings->goalAmount ?? 0;
        $this->goalProgressType = $this->formSettings->goalProgressType ?? GoalProgressType::ALL_TIME();
        $this->goalStartDate = $this->formSettings->goalStartDate ?? null;
        $this->goalEndDate = $this->formSettings->goalEndDate ?? null;
        $this->campaign = Campaign::findByFormId($this->formId);
    }

    /**
     * @unreleased switch between Campaign goal and Form goal
     * @since      3.0.0
     *
     * @return int|float
     */
    public function getCurrentAmount()
    {
        $query = $this->goalSource->isCampaign()
            ? $this->getCampaignQuery()
            : $this->getFormQuery();

        switch ($this->getGoalType()->getValue()):
            case 'donors':
            case 'donorsFromSubscriptions':
                return $query->countDonors();
            case 'donations':
                return $this->goalSource->isCampaign()
                    ? $query->countDonations()
                    : $query->count();
            case 'subscriptions':
                return $query->count();
            case 'amountFromSubscriptions':
                return $query->sumInitialAmount();
            default:
                return $query->sumIntendedAmount();
        endswitch;
    }

    /**
     * Check if goal type is subscription
     *
     * @unreleased
     */
    private function isSubscription(): bool
    {
        return in_array(
            $this->getGoalType()->getValue(), [
            'subscriptions',
            'amountFromSubscriptions',
            'donorsFromSubscriptions',
        ], true);
    }

    /**
     * Get Campaign query
     *
     * @unreleased
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
     * @unreleased
     *
     * @return DonationQuery|SubscriptionQuery
     */
    private function getFormQuery()
    {
        $query = $this->isSubscription()
            ? new SubscriptionQuery()
            : new DonationQuery();

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
     * @unreleased
     *
     * @return float|int
     */
    public function getTargetAmount()
    {
        return $this->goalSource->isCampaign()
            ? $this->campaign->goal
            : $this->targetAmount;
    }

    /**
     * Check if goal type is an amount
     *
     * @unreleased
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
     * @since 3.0.0
     */
    public function toArray(): array
    {
        $currentAmount = $this->getCurrentAmount();
        $targetAmount = $this->getTargetAmount();
        $goalTypeIsAmount = $this->isAmount();

        $progressPercentage = ! $currentAmount || ! $targetAmount ? 0 : ($currentAmount / $targetAmount) * 100;

        return [
            'type' => $this->goalType->getValue(),
            'typeIsCount' => ! $goalTypeIsAmount,
            'typeIsMoney' => $goalTypeIsAmount,
            'enabled' => $this->isEnabled,
            'show' => $this->isEnabled,
            'currentAmount' => $currentAmount,
            'targetAmount' => $targetAmount,
            'label' => $this->getLabel(),
            'isAchieved' => $this->isEnabled && $this->formSettings->enableAutoClose && $progressPercentage >= 100,
        ];
    }
}
