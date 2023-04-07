<?php

namespace Give\NextGen\DonationForm\Actions;

use Give\Donations\ValueObjects\DonationType;
use Give\Framework\FieldsAPI\Amount;
use Give\Framework\FieldsAPI\DonationAmount;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Hidden;
use Give\Framework\FieldsAPI\Option;
use Give\Framework\FieldsAPI\Radio;
use Give\NextGen\DonationForm\Rules\DonationTypeRule;
use Give\NextGen\DonationForm\Rules\Max;
use Give\NextGen\DonationForm\Rules\Min;
use Give\NextGen\DonationForm\Rules\Size;
use Give\NextGen\DonationForm\Rules\SubscriptionFrequencyRule;
use Give\NextGen\DonationForm\Rules\SubscriptionInstallmentsRule;
use Give\NextGen\DonationForm\Rules\SubscriptionPeriodRule;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;


class ConvertDonationAmountBlockToFieldsApi
{

    /**
     * @since 0.3.0
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
            $amountNode
                ->label(__('Donation Amount', 'give'))
                ->levels(...array_map('absint', $block->getAttribute('levels')))
                ->allowLevels($block->getAttribute('priceOption') === 'multi')
                ->allowCustomAmount($block->getAttribute('customAmount'))
                ->fixedAmountValue($block->getAttribute('setPrice'))
                ->defaultValue(
                    $block->getAttribute('priceOption') === 'set' ?
                        $block->getAttribute('setPrice') : 50
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

            $amountField
                ->enableSubscriptions()
                ->subscriptionDetailsAreFixed($block->getAttribute('recurringDonationChoice') === 'admin')
                ->donationType($donationType)
                ->subscriptionPeriod($subscriptionPeriod)
                ->subscriptionFrequency($subscriptionFrequency)
                ->subscriptionInstallments($subscriptionInstallments);
        }

        return $amountField;
    }

    /**
     * @since 0.3.0
     *
     * @throws EmptyNameException
     */
    protected function getRecurringAmountPeriodField(BlockModel $block): Field
    {
        $donationChoice = $block->getAttribute('recurringDonationChoice');

        // if admin - fields are all hidden
        if ($donationChoice === 'admin') {
            $recurringBillingPeriod = new SubscriptionPeriod($block->getAttribute('recurringBillingPeriod'));

            return Hidden::make('subscriptionPeriod')
                ->defaultValue($recurringBillingPeriod->getValue())
                ->rules(new SubscriptionPeriodRule());
        }

        $recurringBillingPeriodOptions = $block->getAttribute('recurringBillingPeriodOptions');

        $options = $this->mergePeriodOptionsWithOneTime(
            array_map(static function ($option) {
                $subscriptionPeriod = new SubscriptionPeriod($option);
                
                return new Option($subscriptionPeriod->getValue(), $subscriptionPeriod->label(0));
            }, $recurringBillingPeriodOptions)
        );

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

    /**
     * @since 0.3.0
     */
    protected function mergePeriodOptionsWithOneTime(array $options): array
    {
        return array_merge([
            new Option('one-time', __('One Time', 'give'))
        ], $options);
    }
}