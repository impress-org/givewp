<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\DonationForms\DonationQuery;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\SubscriptionQuery;
use Give\DonationForms\ValueObjects\GoalProgressType;
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
     * @since 3.0.0
     */
    public function __construct(int $formId, FormSettings $formSettings)
    {
        $this->formId = $formId;
        $this->formSettings = $formSettings;
        $this->isEnabled = $formSettings->enableDonationGoal ?? false;
        $this->goalType = $formSettings->goalType ?? GoalType::AMOUNT();
        $this->targetAmount = $this->formSettings->goalAmount ?? 0;
        $this->goalProgressType = $this->formSettings->goalProgressType ?? GoalProgressType::ALL_TIME();
        $this->goalStartDate = $this->formSettings->goalStartDate ?? null;
        $this->goalEndDate = $this->formSettings->goalEndDate ?? null;
    }

    /**
     * @since 3.0.0
     *
     * @return int|float
     */
    public function getCurrentAmount()
    {
        $query = $this->goalType->isOneOf(GoalType::SUBSCRIPTIONS(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS(), GoalType::DONORS_FROM_SUBSCRIPTIONS())
            ? new SubscriptionQuery()
            : new DonationQuery();

        $query->form($this->formId);

        if($this->goalProgressType->isCustom()) {
            $query->between($this->goalStartDate, $this->goalEndDate);
        }

        switch ($this->goalType):
            case GoalType::DONORS():
                return $query->countDonors();
            case GoalType::DONATIONS():
                return $query->count();
            case GoalType::SUBSCRIPTIONS():
                return $query->count();
            case GoalType::AMOUNT_FROM_SUBSCRIPTIONS():
                return $query->sumInitialAmount();
            case GoalType::DONORS_FROM_SUBSCRIPTIONS():
                return $query->countDonors();
            case GoalType::AMOUNT():
            default:
                return $query->sumIntendedAmount();
        endswitch;
    }

    /**
     * @since 3.0.0
     */
    public function getLabel(): string
    {
        if ($this->goalType->isDonors() || $this->goalType->isDonorsFromSubscriptions()) {
            return __('donors', 'give');
        }

        if ($this->goalType->isDonations()) {
            return __('donations', 'give');
        }

        if ($this->goalType->isSubscriptions()) {
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
        $progressPercentage = !$currentAmount || !$this->targetAmount ? 0 : ($currentAmount / $this->targetAmount) * 100;
        $goalTypeIsAmount = $this->goalType->isOneOf(GoalType::AMOUNT(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS());

        return [
            'type' => $this->goalType->getValue(),
            'typeIsCount' => !$goalTypeIsAmount,
            'typeIsMoney' => $goalTypeIsAmount,
            'enabled' => $this->isEnabled,
            'show' => $this->isEnabled,
            'currentAmount' => $currentAmount,
            'targetAmount' => $this->targetAmount,
            'label' => $this->getLabel(),
            'isAchieved' => $this->isEnabled && $this->formSettings->enableAutoClose && $progressPercentage >= 100
        ];
    }
}
