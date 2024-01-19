<?php

namespace Give\FormBuilder\BlockTypes;

use Give\Framework\Blocks\BlockType;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

/**
 * @unreleased
 *
 * @property string $label
 * @property array $levels
 * @property float $defaultLevel
 * @property string $priceOption
 * @property int $setPrice
 * @property bool $customAmount
 * @property int $customAmountMin
 * @property int $customAmountMax
 * @property bool $recurringEnabled
 * @property int $recurringBillingInterval
 * @property array $recurringBillingPeriodOptions
 * @property int $recurringLengthOfTime
 * @property bool $recurringEnableOneTimeDonations
 * @property string $recurringOptInDefaultBillingPeriod
 */
class DonationAmountBlockType extends BlockType
{
    /**
     * @unreleased
     */
    public function getName(): string
    {
        return 'givewp/donation-amount';
    }

    /**
     * @unreleased
     */
    protected $properties = [
        'label' => 'string',
        'levels' => 'array',
        'defaultLevel' => 'float',
        'priceOption' => 'string',
        'setPrice' => 'int',
        'customAmount' => 'bool',
        'customAmountMin' => 'int',
        'customAmountMax' => 'int',
        'recurringEnabled' => 'bool',
        'recurringBillingInterval' => 'int',
        'recurringBillingPeriodOptions' => 'array',
        'recurringLengthOfTime' => 'int',
        'recurringEnableOneTimeDonations' => 'bool',
        'recurringOptInDefaultBillingPeriod' => 'string',
    ];

    /**
     * @unreleased
     *
     * @return float[]
     */
    public function getLevels(): array
    {
        return array_map(static function ($level) {
            return (float)filter_var($level, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }, $this->levels);
    }

    /**
     * @return bool
     */
    public function isRecurringFixed(): bool
    {
        return count($this->recurringBillingPeriodOptions) === 1 && $this->recurringEnableOneTimeDonations === false;
    }

    /**
     * @unreleased
     */
    public function setRecurringEnabled(bool $enabled = true): self
    {
        $this->recurringEnabled = $enabled;

        return $this;
    }

    /**
     * @unreleased
     */
    public function setRecurringEnableOneTimeDonations(bool $enabled = true): self
    {
        $this->recurringEnableOneTimeDonations = $enabled;

        return $this;
    }

    /**
     * @unreleased
     */
    public function setRecurringBillingInterval(int $interval): self
    {
        $this->recurringBillingInterval = $interval;

        return $this;
    }

    /**
     * @unreleased
     */
    public function setRecurringLengthOfTime(int $lengthOfTime): self
    {
        $this->recurringLengthOfTime = $lengthOfTime;

        return $this;
    }

    /**
     * @unreleased
     */
    public function setRecurringBillingPeriodOptions(SubscriptionPeriod ...$options): self
    {
        $this->recurringBillingPeriodOptions =
            array_values(
                array_map(static function (SubscriptionPeriod $option) {
                    return $option->getValue();
                }, $options)
            );

        return $this;
    }

    /**
     * @unreleased
     */
    public function setRecurringOptInDefaultBillingPeriod(SubscriptionPeriod $period): self
    {
        $this->recurringOptInDefaultBillingPeriod = $period->getValue();

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function isCustomAmountEnabled(): bool
    {
        return $this->customAmount === true;
    }
}
