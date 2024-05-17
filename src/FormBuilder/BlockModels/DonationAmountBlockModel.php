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
}
