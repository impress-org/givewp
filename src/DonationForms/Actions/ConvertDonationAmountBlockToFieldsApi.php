<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Rules\DonationTypeRule;
use Give\DonationForms\Rules\Max;
use Give\DonationForms\Rules\Min;
use Give\DonationForms\Rules\Size;
use Give\DonationForms\Rules\SubscriptionFrequencyRule;
use Give\DonationForms\Rules\SubscriptionInstallmentsRule;
use Give\DonationForms\Rules\SubscriptionPeriodRule;
use Give\Donations\ValueObjects\DonationType;
use Give\FormBuilder\BlockModels\DonationAmountBlockModel;
use Give\Framework\FieldsAPI\Amount;
use Give\Framework\FieldsAPI\DonationAmount;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Hidden;
use Give\Framework\FieldsAPI\Option;
use Give\Framework\FieldsAPI\Radio;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;


class ConvertDonationAmountBlockToFieldsApi
{

    /**
     * @since 3.0.0
     *
     * @throws EmptyNameException
     * @throws NameCollisionException
     */
    public function __invoke(DonationAmountBlockModel $block, string $currency): DonationAmount
    {
        $amountField = DonationAmount::make('donationAmount')->tap(function (Group $group) use ($block, $currency) {
            $amountRules = ['required', 'numeric'];

            if (!$block->isCustomAmountEnabled() &&
                $block->getPriceOption() === 'set') {
                $size = $block->getSetPrice();

                $amountRules[] = new Size($size);
            }

            if ($block->isCustomAmountEnabled()) {
                if ($block->hasAttribute('customAmountMin')) {
                    $amountRules[] = new Min($block->getAttribute('customAmountMin'));
                }

                if ($block->hasAttribute('customAmountMax') && $block->getAttribute('customAmountMax') > 0) {
                    $amountRules[] = new Max($block->getAttribute('customAmountMax'));
                }
            }

            /** @var Amount $amountNode */
            $amountNode = $group->getNodeByName('amount');
            $defaultLevel = $block->getDefaultLevel() > 0 ? $block->getDefaultLevel() : 10;
            $amountNode
                ->label($block->getLabel())
                ->levels(...$block->getLevels())
                ->allowLevels($block->getPriceOption() === 'multi')
                ->allowCustomAmount($block->isCustomAmountEnabled())
                ->fixedAmountValue($block->getSetPrice())
                ->defaultValue(
                    $block->getPriceOption() === 'set' ?
                        $block->getSetPrice() : $defaultLevel
                )
                ->rules(...$amountRules);

            /** @var Hidden $currencyNode */
            $currencyNode = $group->getNodeByName('currency');
            $currencyNode
                ->defaultValue($currency)
                ->rules('required', 'currency');
        });

        if (!$block->isRecurringEnabled()) {
            $donationType = Hidden::make('donationType')
                ->defaultValue(DonationType::SINGLE()->getValue())
                ->rules(new DonationTypeRule());

            $amountField->donationType($donationType);
        } else {
            $subscriptionPeriod = $this->getRecurringAmountPeriodField($block);

            $donationTypeDefault = $subscriptionPeriod->getDefaultValue() === 'one-time' ? DonationType::SINGLE(
            )->getValue() : DonationType::SUBSCRIPTION()->getValue();

            $donationType = Hidden::make('donationType')
                ->defaultValue($donationTypeDefault)
                ->rules(new DonationTypeRule());

            $subscriptionFrequency = Hidden::make('subscriptionFrequency')
                ->defaultValue($block->getRecurringBillingInterval())
                ->rules(new SubscriptionFrequencyRule());

            $subscriptionInstallments = Hidden::make('subscriptionInstallments')
                ->defaultValue($block->getRecurringLengthOfTime())
                ->rules(new SubscriptionInstallmentsRule());

            $amountField
                ->enableSubscriptions()
                ->subscriptionDetailsAreFixed($block->isRecurringFixed())
                ->donationType($donationType)
                ->subscriptionPeriod($subscriptionPeriod)
                ->subscriptionFrequency($subscriptionFrequency)
                ->subscriptionInstallments($subscriptionInstallments);
        }

        return $amountField;
    }

    /**
     * @since 3.0.0
     *
     * @throws EmptyNameException
     */
    protected function getRecurringAmountPeriodField(DonationAmountBlockModel $block): Field
    {
        $recurringBillingPeriodOptions = $block->getRecurringBillingPeriodOptions();

        // if recurring is fixed - fields are all hidden
        if ($block->isRecurringFixed()) {
            $fixedBillingPeriod = $recurringBillingPeriodOptions[0];

            $subscriptionPeriodDefaultValue = SubscriptionPeriod::isValid(
                $fixedBillingPeriod
            ) ? (new SubscriptionPeriod($fixedBillingPeriod))->getValue() : SubscriptionPeriod::MONTH()->getValue();

            return Hidden::make('subscriptionPeriod')
                ->defaultValue($subscriptionPeriodDefaultValue)
                ->rules(new SubscriptionPeriodRule());
        }

        if ($block->isRecurringEnableOneTimeDonations()) {
            $recurringBillingPeriodOptions = array_merge(['one-time'], $recurringBillingPeriodOptions);
        }

        $options = array_map(static function ($option) {
            if (SubscriptionPeriod::isValid($option)) {
                $subscriptionPeriod = new SubscriptionPeriod($option);

                return new Option($subscriptionPeriod->getValue(), $subscriptionPeriod->label(0));
            }

            return new Option($option, $option === 'one-time' ? __('One Time', 'give') : ucfirst($option));
        }, $recurringBillingPeriodOptions);

        $recurringOptInDefault = $block->getRecurringOptInDefaultBillingPeriod();

        if (SubscriptionPeriod::isValid($recurringOptInDefault)) {
            $subscriptionPeriod = new SubscriptionPeriod($recurringOptInDefault);

            $defaultValue = $subscriptionPeriod->getValue();
        } else {
            $defaultValue = 'one-time';
        }

        return Radio::make('subscriptionPeriod')
            ->defaultValue($defaultValue)
            ->label(__('Choose your donation frequency', 'give'))
            ->options(...$options)
            ->rules(new SubscriptionPeriodRule());
    }
}
