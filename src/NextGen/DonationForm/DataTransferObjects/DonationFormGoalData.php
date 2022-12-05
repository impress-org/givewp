<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\Framework\Support\Contracts\Arrayable;
use Give\NextGen\DonationForm\Properties\FormSettings;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\DonationForm\ValueObjects\GoalType;

/**
 * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    public function toArray(): array
    {
        $currentAmount = $this->getCurrentAmount();

        return [
            'type' => $this->goalType->getValue(),
            'typeIsCount' => !$this->goalType->isAmount(),
            'typeIsMoney' => $this->goalType->isAmount(),
            'enabled' => $this->isEnabled,
            'show' => $this->isEnabled,
            'currentAmount' => $currentAmount,
            'targetAmount' => $this->targetAmount,
            'label' => $this->goalType->isDonors() ? __('donors', 'give') : __('donations', 'give'),
            'progressPercentage' => !$currentAmount || !$this->targetAmount ? 0 : ($currentAmount / $this->targetAmount) * 100
        ];
    }
}
