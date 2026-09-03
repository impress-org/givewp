<?php

namespace Give\FormBuilder\BlockModels;

use Give\Framework\Blocks\BlockModel;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

/**
 * This is a decorator for the Block Model block "givewp/donation-amount".
 *
 * @since 3.0.0
 */
class DonationAmountBlockModel
{
    /**
     * @var BlockModel
     */
    public $block;

    /**
     * @since 3.0.0
     */
    public function __construct(BlockModel $block)
    {
        $this->block = $block;
    }

    /**
     * @since 3.0.0
     */
    public function getAttribute($name)
    {
        return $this->block->getAttribute($name);
    }

    /**
     * @since 3.0.0
     */
    public function hasAttribute($name): bool
    {
        return $this->block->hasAttribute($name);
    }

    /**
     * @since 3.0.0
     */
    public function setAttribute(string $name, $value): self
    {
        $this->block->setAttribute($name, $value);

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function getLabel(): string
    {
        return $this->block->getAttribute('label');
    }

    /**
     * @since 3.12.0 Changed the return type to an array of OptionsProps
     * @since 3.0.0
     *
     * @return array ['label' => string, 'value' => string, 'checked' => bool][]
     */
    public function getLevels(): array
    {
        return array_map(static function ($level) {
            return [
                'label'   => htmlspecialchars($level['label'] ?? ''),
                'value'   => (float)filter_var(
                    $level['value'] ?? '',
                    FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION
                ),
                'checked' => (bool)filter_var($level['checked'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }, $this->block->getAttribute('levels'));
    }

    /**
     * @since 3.12.0
     */
    public function isDescriptionEnabled(): bool
    {
        return $this->block->getAttribute('descriptionsEnabled') === true;
    }

    /**
     * @return bool
     */
    public function isRecurringFixed(): bool
    {
        return count($this->block->getAttribute('recurringBillingPeriodOptions')) === 1 && $this->block->getAttribute(
                'recurringEnableOneTimeDonations'
            ) === false;
    }

    /**
     * @since 3.0.0
     */
    public function getRecurringBillingInterval(): int
    {
        return (int)$this->block->getAttribute('recurringBillingInterval');
    }

    /**
     * @since 3.0.0
     */
    public function getRecurringLengthOfTime(): int
    {
        return (int)$this->block->getAttribute('recurringLengthOfTime');
    }

    /**
     * @since 3.0.0
     */
    public function getRecurringOptInDefaultBillingPeriod(): string
    {
        return $this->block->getAttribute('recurringOptInDefaultBillingPeriod');
    }

    /**
     * @since 3.0.0
     */
    public function getRecurringBillingPeriodOptions(): array
    {
        return $this->block->getAttribute('recurringBillingPeriodOptions');
    }

    /**
     * @since 3.0.0
     */
    public function isRecurringEnableOneTimeDonations(): bool
    {
        return $this->block->getAttribute('recurringEnableOneTimeDonations') === true;
    }

    /**
     * @since 3.0.0
     */
    public function isRecurringEnabled(): bool
    {
        return $this->block->getAttribute('recurringEnabled') === true;
    }

    /**
     * @since 3.0.0
     */
    public function setRecurringEnabled(bool $enabled = true): self
    {
        return $this->setAttribute('recurringEnabled', $enabled);
    }

    /**
     * @since 3.0.0
     */
    public function setRecurringEnableOneTimeDonations(bool $enabled = true): self
    {
        return $this->setAttribute('recurringEnableOneTimeDonations', $enabled);
    }

    /**
     * @since 3.0.0
     */
    public function setRecurringBillingInterval(int $interval): self
    {
        return $this->setAttribute('recurringBillingInterval', $interval);
    }

    /**
     * @since 3.0.0
     */
    public function setRecurringLengthOfTime(int $lengthOfTime): self
    {
        return $this->setAttribute('recurringLengthOfTime', $lengthOfTime);
    }

    /**
     * @since 3.0.0
     */
    public function setRecurringBillingPeriodOptions(SubscriptionPeriod ...$options): self
    {
        return $this->setAttribute(
            'recurringBillingPeriodOptions',
            array_values(
                array_map(static function (SubscriptionPeriod $option) {
                    return $option->getValue();
                }, $options)
            )
        );
    }

    /**
     * @since 3.0.0
     */
    public function setRecurringOptInDefaultBillingPeriod(SubscriptionPeriod $period): self
    {
        return $this->setAttribute('recurringOptInDefaultBillingPeriod', $period->getValue());
    }

    /**
     * @since 3.0.0
     */
    public function isCustomAmountEnabled(): bool
    {
        return $this->block->getAttribute('customAmount') === true;
    }

    /**
     * @since 3.0.0
     */
    public function getPriceOption(): string
    {
        return $this->block->getAttribute('priceOption');
    }

    /**
     * @since 3.0.0
     */
    public function getSetPrice(): int
    {
        return $this->block->getAttribute('setPrice');
    }

    /**
     * The lowest amount a donor may type into the custom amount input, or zero when the block does not
     * define one.
     *
     * @since 4.16.8
     */
    public function getCustomAmountMin(): float
    {
        return $this->hasAttribute('customAmountMin') ? (float)$this->getAttribute('customAmountMin') : 0.0;
    }

    /**
     * The highest amount a donor may type into the custom amount input, or zero when the block does not
     * define one.
     *
     * @since 4.16.8
     */
    public function getCustomAmountMax(): float
    {
        return $this->hasAttribute('customAmountMax') ? (float)$this->getAttribute('customAmountMax') : 0.0;
    }

    /**
     * The amounts the admin configured on the block: the donation levels, or the fixed set price. The
     * custom amount minimum and maximum never apply to these.
     *
     * @since 4.16.8
     *
     * @return float[]
     */
    public function getAdminDefinedAmounts(): array
    {
        $amounts = $this->getPriceOption() === 'multi'
            ? array_column($this->getLevels(), 'value')
            : [$this->getSetPrice()];

        // An unset level or set price sanitizes to 0, which must never be exempt from the minimum.
        return array_values(
            array_filter(
                array_map('floatval', $amounts),
                static function (float $amount): bool {
                    return $amount > 0;
                }
            )
        );
    }

    /**
     * The custom amount minimum, falling back to the lowest amount the admin configured. Without that
     * fallback a form that leaves the minimum empty accepts any amount, which is a donation spam and card
     * testing vector.
     *
     * @since 4.16.8
     *
     * @return float|null Null when the block defines neither a minimum nor an amount to derive one from.
     */
    public function getMinimumAmount(): ?float
    {
        if ($this->isCustomAmountEnabled() && $this->getCustomAmountMin() > 0) {
            return $this->getCustomAmountMin();
        }

        $adminDefinedAmounts = $this->getAdminDefinedAmounts();
        $lowestAmount = $adminDefinedAmounts ? min($adminDefinedAmounts) : 0.0;

        return $lowestAmount > 0 ? $lowestAmount : null;
    }
}
