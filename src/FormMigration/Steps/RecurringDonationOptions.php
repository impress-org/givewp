<?php

namespace Give\FormMigration\Steps;

use Give\FormBuilder\BlockModels\DonationAmountBlockModel;
use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

/**
 * @since 3.0.0
 */
class RecurringDonationOptions extends FormMigrationStep
{
    /**
     * @since 3.0.0
     */
    public function canHandle(): bool
    {
        return $this->formV2->isRecurringDonationsEnabled();
    }

    /**
     * @since 3.0.0
     */
    public function process()
    {
        $block = $this->fieldBlocks->findByName('givewp/donation-amount');
        $amountBlock = new DonationAmountBlockModel($block);

        switch ($this->formV2->getRecurringDonationsOption()) {
            case 'no':
                $amountBlock->setRecurringEnabled(false);
                break;
            case 'yes_donor':
                $this->handleDonorChoice($amountBlock);
                break;
            case 'yes_admin':
                $this->handleAdminDefined($amountBlock);
                break;
            default:
                break;
        }
    }

    /**
     * Donor's choice has its own set of options for period functionality and default period (checkbox opt-in).
     *
     * @since 3.0.0
     */
    protected function handleDonorChoice(DonationAmountBlockModel $amountBlock)
    {
        $amountBlock->setRecurringEnabled();
        $amountBlock->setRecurringEnableOneTimeDonations();

        // donor's choice of billing period means the donor selects the billing period
        if ($this->formV2->isRecurringPeriodFunctionalityDonorsChoice()) {
            // add all available subscription billing period options
            $amountBlock->setRecurringBillingPeriodOptions(...SubscriptionPeriod::values());

            if ($this->formV2->isRecurringDefaultCheckboxEnabled()) {
                $defaultPeriod = $this->getBillingPeriodFromMeta(
                    $this->formV2->getRecurringPeriodDefaultDonorsChoice()
                );

                $amountBlock->setRecurringOptInDefaultBillingPeriod($defaultPeriod);
            } else {
                $amountBlock->setAttribute('recurringOptInDefaultBillingPeriod', 'one-time');
            }
            // admins choice of billing period means the admin selects the billing period
        } elseif ($this->formV2->isRecurringPeriodFunctionalityAdminChoice()) {
            $defaultBillingPeriod = $this->getBillingPeriodFromMeta($this->formV2->getRecurringPeriod());

            $amountBlock->setRecurringBillingPeriodOptions($defaultBillingPeriod);

            if ($this->formV2->isRecurringDefaultCheckboxEnabled()) {
                $amountBlock->setRecurringOptInDefaultBillingPeriod($defaultBillingPeriod);
            } else {
                $amountBlock->setAttribute('recurringOptInDefaultBillingPeriod', 'one-time');
            }
        }

        if (!empty($recurringBillingInterval = $this->formV2->getRecurringBillingInterval())) {
            $amountBlock->setRecurringBillingInterval($recurringBillingInterval);
        }

        if (!empty($recurringLengthOfTime = $this->formV2->getRecurringLengthOfTime())) {
            $amountBlock->setRecurringLengthOfTime($recurringLengthOfTime);
        }
    }

    /**
     * Admins choice works differently depending on the donation option value and if custom amount is enabled.
     * If donation option is 'set', then it uses the general recurring options.
     * If donation option is 'multi' and custom amount is enabled, then it uses the custom amount recurring options.
     * If donation option is 'multi' and custom amount is disabled, then it uses the first donation level recurring options.
     *
     * @since 3.0.0
     */
    protected function handleAdminDefined(DonationAmountBlockModel $amountBlock)
    {
        $amountBlock->setRecurringEnabled();
        $amountBlock->setRecurringEnableOneTimeDonations(false);

        if ($this->formV2->isDonationOptionSet()) {
            $billingPeriod = $this->getBillingPeriodFromMeta($this->formV2->getRecurringPeriod());

            $amountBlock->setRecurringLengthOfTime($this->formV2->getRecurringLengthOfTime());
            $amountBlock->setRecurringBillingInterval($this->formV2->getRecurringBillingInterval());
            $amountBlock->setRecurringBillingPeriodOptions($billingPeriod);
            $amountBlock->setRecurringOptInDefaultBillingPeriod($billingPeriod);
        } elseif ($this->formV2->isDonationOptionMulti()) {
            if ($this->formV2->isCustomAmountOptionEnabled()) {
                $billingPeriod = $this->getBillingPeriodFromMeta($this->formV2->getRecurringCustomAmountPeriod());

                $amountBlock->setRecurringLengthOfTime($this->formV2->getRecurringCustomAmountTimes());
                $amountBlock->setRecurringBillingInterval($this->formV2->getRecurringCustomAmountInterval());
                $amountBlock->setRecurringBillingPeriodOptions($billingPeriod);
                $amountBlock->setRecurringOptInDefaultBillingPeriod($billingPeriod);
            } else {
                // get from donation levels
                $donationLevels = $this->formV2->getDonationLevels();

                if (!empty($donationLevels) && $donationLevels[0]['_give_recurring'] === 'yes') {
                    $level = $donationLevels[0];
                    $amountBlock->setRecurringLengthOfTime((int)$level["_give_times"]);
                    $amountBlock->setRecurringBillingInterval((int)$level["_give_period_interval"]);

                    $period = $this->getBillingPeriodFromMeta($level["_give_period"]);
                    $amountBlock->setRecurringBillingPeriodOptions($period);
                    $amountBlock->setRecurringOptInDefaultBillingPeriod($period);
                }
            }
        }
    }

    /**
     * @since 3.0.0
     */
    protected function getBillingPeriodFromMeta(string $period): SubscriptionPeriod
    {
        return SubscriptionPeriod::isValid($period) ? new SubscriptionPeriod($period) : SubscriptionPeriod::MONTH();
    }
}
