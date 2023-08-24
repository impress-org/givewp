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
use Give\Framework\Blocks\BlockModel;
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
    public function __invoke(BlockModel $block, string $currency): DonationAmount
    {
        $amountField = DonationAmount::make('donationAmount')->tap(function (Group $group) use ($block, $currency) {
            $amountRules = ['required', 'numeric'];

            if (!$block->getAttribute('customAmount') &&
                $block->getAttribute('priceOption') === 'set') {
                $size = $block->getAttribute('setPrice');

                $amountRules[] = new Size($size);
            }

            if ($block->getAttribute('customAmount')) {
                if ($block->hasAttribute('customAmountMin')) {
                    $amountRules[] = new Min($block->getAttribute('customAmountMin'));
                }

                if ($block->hasAttribute('customAmountMax') && $block->getAttribute('customAmountMax') > 0) {
                    $amountRules[] = new Max($block->getAttribute('customAmountMax'));
                }
            }

            /** @var Amount $amountNode */
            $amountNode = $group->getNodeByName('amount');
            $defaultLevel = (float)$block->getAttribute('defaultLevel') > 0 ? (float)$block->getAttribute('defaultLevel') : 10;
            $amountNode
                ->label($block->getAttribute('label'))
                ->levels(...array_map('absint', $block->getAttribute('levels')))
                ->allowLevels($block->getAttribute('priceOption') === 'multi')
                ->allowCustomAmount($block->getAttribute('customAmount'))
                ->fixedAmountValue($block->getAttribute('setPrice'))
                ->defaultValue(
                    $block->getAttribute('priceOption') === 'set' ?
                        $block->getAttribute('setPrice') : $defaultLevel
                )
                ->rules(...$amountRules);

            /** @var Hidden $currencyNode */
            $currencyNode = $group->getNodeByName('currency');
            $currencyNode
                ->defaultValue($currency)
                ->rules('required', 'currency');
        });

        if (!$block->getAttribute('recurringEnabled')) {
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

            $billingInterval = (int)$block->getAttribute('recurringBillingInterval');
            $lengthOfTime = (int)$block->getAttribute('recurringLengthOfTime');

            $subscriptionFrequency = Hidden::make('subscriptionFrequency')
                ->defaultValue($billingInterval)
                ->rules(new SubscriptionFrequencyRule());

            $subscriptionInstallments = Hidden::make('subscriptionInstallments')
                ->defaultValue($lengthOfTime)
                ->rules(new SubscriptionInstallmentsRule());

            $recurringBillingPeriodOptions = $block->getAttribute('recurringBillingPeriodOptions');
            $subscriptionDetailsAreFixed = count($recurringBillingPeriodOptions) === 1 && $block->getAttribute('recurringEnableOneTimeDonations') === false;

            $amountField
                ->enableSubscriptions()
                ->subscriptionDetailsAreFixed($subscriptionDetailsAreFixed)
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
    protected function getRecurringAmountPeriodField(BlockModel $block): Field
    {
        $recurringBillingPeriodOptions = $block->getAttribute('recurringBillingPeriodOptions');
        $subscriptionDetailsAreFixed = count($recurringBillingPeriodOptions) === 1 && $block->getAttribute('recurringEnableOneTimeDonations') === false;

        // if admin - fields are all hidden
        if ($subscriptionDetailsAreFixed) {
            $fixedBillingPeriod = $recurringBillingPeriodOptions[0];

            $subscriptionPeriodDefaultValue = SubscriptionPeriod::isValid($fixedBillingPeriod) ? (new SubscriptionPeriod($fixedBillingPeriod))->getValue() : SubscriptionPeriod::MONTH()->getValue();

            return Hidden::make('subscriptionPeriod')
                    ->defaultValue($subscriptionPeriodDefaultValue)
                    ->rules(new SubscriptionPeriodRule());
        }

        if ($block->getAttribute('recurringEnableOneTimeDonations')) {
            $recurringBillingPeriodOptions = array_merge(['one-time'], $recurringBillingPeriodOptions);
        }

        $options = array_map(static function ($option) {
            if (SubscriptionPeriod::isValid($option)) {
                $subscriptionPeriod = new SubscriptionPeriod($option);

                return new Option($subscriptionPeriod->getValue(), $subscriptionPeriod->label(0));
            }

            return new Option($option, $option === 'one-time' ? __('One Time', 'give') : ucfirst($option));
        }, $recurringBillingPeriodOptions);

        $recurringOptInDefault = $block->getAttribute('recurringOptInDefaultBillingPeriod');

        if (!empty($recurringOptInDefault) && $recurringOptInDefault !== 'one-time') {
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
