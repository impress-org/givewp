<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\Repositories\DonationFormRepository;
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
     * @since 3.0.0
     */
    public function __construct(int $formId, FormSettings $formSettings)
    {
        $this->formId = $formId;
        $this->formSettings = $formSettings;
        $this->isEnabled = $formSettings->enableDonationGoal ?? false;
        $this->goalType = $formSettings->goalType ?? GoalType::AMOUNT();
        $this->targetAmount = $this->formSettings->goalAmount ?? 0;
    }

    /**
     * @since 3.0.0
     */
    public function getCurrentAmount(): int
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

        if ($this->goalType->isDonors()) {
            return $donationFormRepository->getTotalNumberOfDonors($this->formId);
        }

        if ($this->goalType->isDonations()) {
            return $donationFormRepository->getTotalNumberOfDonations($this->formId);
        }

        return $donationFormRepository->getTotalRevenue($this->formId);
    }

    /**
     * @since 3.0.0
     *
     * @return int|float
     */
    public function getCurrentAmountFromSubscriptions()
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

        if ($this->goalType->isDonors()) {
            return $donationFormRepository->getTotalNumberOfDonorsFromSubscriptionInitialDonations($this->formId);
        }

        if ($this->goalType->isDonations()) {
            return $donationFormRepository->getTotalNumberOfSubscriptionInitialDonations($this->formId);
        }

        return $donationFormRepository->getTotalRevenueFromSubscriptions($this->formId);
    }

    /**
     * @since 3.0.0
     */
    public function toArray(): array
    {
        $currentAmount = $this->formSettings->goalShouldOnlyCountRecurringDonations ? $this->getCurrentAmountFromSubscriptions() : $this->getCurrentAmount();
        $progressPercentage = !$currentAmount || !$this->targetAmount ? 0 : ($currentAmount / $this->targetAmount) * 100;

        return [
            'type' => $this->goalType->getValue(),
            'typeIsCount' => !$this->goalType->isAmount(),
            'typeIsMoney' => $this->goalType->isAmount(),
            'enabled' => $this->isEnabled,
            'show' => $this->isEnabled,
            'currentAmount' => $currentAmount,
            'targetAmount' => $this->targetAmount,
            'label' => $this->goalType->isDonors() ? __('donors', 'give') : __('donations', 'give'),
            'progressPercentage' => $progressPercentage,
            'isAchieved' => $this->isEnabled && $this->formSettings->enableAutoClose && $progressPercentage >= 100
        ];
    }
}
