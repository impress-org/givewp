<?php

namespace Give\FormBuilder\BlockModels;

use Give\Framework\Blocks\BlockModel;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

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
    public function setAttribute(string $name, $value): self
    {
        $this->block->setAttribute($name, $value);

        return $this;
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
    public function setRecurringEnabled(bool $enabled = true): self
    {
        return $this->setAttribute('recurringEnabled', $enabled);
    }

    /**
     * @unreleased
     */
    public function setRecurringEnableOneTimeDonations(bool $enabled = true): self
    {
        return $this->setAttribute('recurringEnableOneTimeDonations', $enabled);
    }

    /**
     * @unreleased
     */
    public function setRecurringBillingInterval(int $interval): self
    {
        return $this->setAttribute('recurringBillingInterval', $interval);
    }

    /**
     * @unreleased
     */
    public function setRecurringLengthOfTime(int $lengthOfTime): self
    {
        return $this->setAttribute('recurringLengthOfTime', $lengthOfTime);
    }

    /**
     * @unreleased
     */
    public function setRecurringBillingPeriodOptions(SubscriptionPeriod ...$options): self
    {
        return $this->setAttribute(
            'recurringBillingPeriodOptions',
            array_map(static function (SubscriptionPeriod $option) {
                return $option->getValue();
            }, $options)
        );
    }

    /**
     * @unreleased
     */
    public function setRecurringOptInDefaultBillingPeriod(SubscriptionPeriod $period): self
    {
        return $this->setAttribute('recurringOptInDefaultBillingPeriod', $period->getValue());
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