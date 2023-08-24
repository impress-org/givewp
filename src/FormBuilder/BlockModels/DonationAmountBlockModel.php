<?php

namespace Give\FormBuilder\BlockModels;

use Give\Framework\Blocks\BlockModel;

/**
 * This is a decorator for the Block Model block "givewp/donation-amount".
 *
 * @unreleased
 */
class DonationAmountBlockModel {
    /**
     * @var BlockModel
     */
    public $block;

    /**
     * @unreleased
     */
    public function __construct(BlockModel $block) {
        $this->block = $block;
    }

    /**
     * @unreleased
     */
    public function getAttribute($name)
    {
        return $this->block->getAttribute($name);
    }

    /**
     * @unreleased
     */
    public function hasAttribute($name): bool
    {
        return $this->block->hasAttribute($name);
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return $this->block->getAttribute('label');
    }

    /**
     * @unreleased
     */
    public function getLevels(): array
    {
        return array_map('absint', $this->block->getAttribute('levels'));
    }

    /**
     * @unreleased
     *
     * @return string|null
     */
    public function getDefaultLevel()
    {
        return $this->block->getAttribute('defaultLevel');
    }

    /**
     * @return bool
     */
    public function isRecurringFixed(): bool
    {
        return count($this->block->getAttribute('recurringBillingPeriodOptions')) === 1 && $this->block->getAttribute('recurringEnableOneTimeDonations') === false;
    }

    /**
     * @unreleased
     */
    public function getRecurringBillingInterval(): int
    {
        return (int)$this->block->getAttribute('recurringBillingInterval');
    }

    /**
     * @unreleased
     */
    public function getRecurringLengthOfTime(): int
    {
        return (int)$this->block->getAttribute('recurringLengthOfTime');
    }

    /**
     * @unreleased
     */
    public function getRecurringOptInDefaultBillingPeriod(): string
    {
        return $this->block->getAttribute('recurringOptInDefaultBillingPeriod');
    }

    /**
     * @unreleased
     */
    public function getRecurringBillingPeriodOptions(): array
    {
        return $this->block->getAttribute('recurringBillingPeriodOptions');
    }

    /**
     * @unreleased
     */
    public function isRecurringEnableOneTimeDonations(): bool
    {
        return $this->block->getAttribute('recurringEnableOneTimeDonations') === true;
    }

    /**
     * @unreleased
     */
    public function isRecurringEnabled(): bool
    {
        return $this->block->getAttribute('recurringEnabled') === true;
    }

    /**
     * @unreleased
     */
    public function isCustomAmountEnabled(): bool
    {
        return $this->block->getAttribute('customAmount') === true;
    }

    /**
     * @unreleased
     */
    public function getPriceOption(): string
    {
        return $this->block->getAttribute('priceOption');
    }

    /**
     * @unreleased
     */
    public function getSetPrice(): int
    {
        return $this->block->getAttribute('setPrice');
    }
}