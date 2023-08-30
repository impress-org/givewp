<?php

namespace Give\FormMigration\Steps;

use Give\FormBuilder\BlockModels\DonationAmountBlockModel;
use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

/**
 * @unreleased
 */
class RecurringDonationOptions extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function canHandle(): bool
    {
        return $this->formV2->isRecurringDonationsEnabled();
    }

    /**
     * @unreleased
     */
    public function process()
    {
        // Recurring Donations = 'no', 'yes_donor', 'yes_admin'
        $_give_recurring = $this->getMetaV2('_give_recurring');

        $block = $this->fieldBlocks->findByName('givewp/donation-amount');
        $amountBlock = new DonationAmountBlockModel($block);

        switch ($_give_recurring) {
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
     * @unreleased
     */
    protected function handleDonorChoice(DonationAmountBlockModel $amountBlock)
    {
        $amountBlock->setRecurringEnabled();
        $amountBlock->setRecurringEnableOneTimeDonations();

        // 'donors_choice', 'admin_choice', 'custom' (The "Donor's Choice" option allows the donor to select the time period (commonly also referred as the "frequency") of their subscription. The "Preset Period" option provides only the selected period for the donor's subscription.)
        $_give_period_functionality = $this->getMetaV2('_give_period_functionality');
        // 'day', 'week', 'month', 'year'
        $_give_period = $this->getMetaV2('_give_period');
        // 'day', 'week', 'month', 'year'
        $_give_period_default_donor_choice = $this->getMetaV2('_give_period_default_donor_choice');
        // 'yes', 'no'
        $_give_checkbox_default = $this->getMetaV2('_give_checkbox_default');
        // integer
        $_give_times = $this->getMetaV2('_give_times');
        // integer
        $_give_period_interval = $this->getMetaV2('_give_period_interval');

        // donor's choice of billing period means the donor selects the billing period
        if ($_give_period_functionality === 'donors_choice') {
            // add all available subscription billing period options
            $amountBlock->setRecurringBillingPeriodOptions(...SubscriptionPeriod::values());

            if ($_give_checkbox_default === 'yes') {
                $defaultPeriod = $this->getBillingPeriodFromMeta($_give_period_default_donor_choice);

                $amountBlock->setRecurringOptInDefaultBillingPeriod($defaultPeriod);
            } else {
                $amountBlock->setAttribute('recurringOptInDefaultBillingPeriod', 'one-time');
            }
            // admins choice of billing period means the admin selects the billing period
        } elseif ($_give_period_functionality === 'admin_choice') {
            $defaultBillingPeriod = $this->getBillingPeriodFromMeta($_give_period);

            $amountBlock->setRecurringBillingPeriodOptions($defaultBillingPeriod);

            if ($_give_checkbox_default === 'yes') {
                $amountBlock->setRecurringOptInDefaultBillingPeriod($defaultBillingPeriod);
            } else {
                $amountBlock->setAttribute('recurringOptInDefaultBillingPeriod', 'one-time');
            }
        }

        if (!empty($_give_period_interval)) {
            $amountBlock->setRecurringBillingInterval((int)$_give_period_interval);
        }

        if (!empty($_give_times)) {
            $amountBlock->setRecurringLengthOfTime((int)$_give_times);
        }
    }

    /**
     * Admins choice works differently depending on the donation option value and if custom amount is enabled.
     * If donation option is 'set', then it uses the general recurring options.
     * If donation option is 'multi' and custom amount is enabled, then it uses the custom amount recurring options.
     * If donation option is 'multi' and custom amount is disabled, then it uses the first donation level recurring options.
     *
     * @unreleased
     */
    protected function handleAdminDefined(DonationAmountBlockModel $amountBlock)
    {
        $amountBlock->setRecurringEnabled();
        $amountBlock->setRecurringEnableOneTimeDonations(false);

        // 'multi', 'set'
        $_give_price_option = $this->getMetaV2('_give_price_option');
        $_give_custom_amount = $this->getMetaV2('_give_custom_amount');

        if ($_give_price_option === 'set') {
            $_give_period = $this->getMetaV2('_give_period');
            $_give_times = $this->getMetaV2('_give_times');
            $_give_period_interval = $this->getMetaV2('_give_period_interval');
            $billingPeriod = $this->getBillingPeriodFromMeta($_give_period);

            $amountBlock->setRecurringLengthOfTime((int)$_give_times);
            $amountBlock->setRecurringBillingInterval((int)$_give_period_interval);
            $amountBlock->setRecurringBillingPeriodOptions($billingPeriod);
            $amountBlock->setRecurringOptInDefaultBillingPeriod($billingPeriod);
        } elseif ($_give_price_option === 'multi') {
            if ($_give_custom_amount === 'enabled') {
                $_give_recurring_custom_amount_period = $this->getMetaV2('_give_recurring_custom_amount_period');
                $_give_recurring_custom_amount_interval = $this->getMetaV2('_give_recurring_custom_amount_interval');
                $_give_recurring_custom_amount_times = $this->getMetaV2('_give_recurring_custom_amount_times');
                $billingPeriod = $this->getBillingPeriodFromMeta($_give_recurring_custom_amount_period);

                $amountBlock->setRecurringLengthOfTime((int)$_give_recurring_custom_amount_times);
                $amountBlock->setRecurringBillingInterval((int)$_give_recurring_custom_amount_interval);
                $amountBlock->setRecurringBillingPeriodOptions($billingPeriod);
                $amountBlock->setRecurringOptInDefaultBillingPeriod($billingPeriod);
            } else {
                // get from donation levels
                $_give_donation_levels = $this->getMetaV2('_give_donation_levels');

                if (!empty($_give_donation_levels) && $_give_donation_levels[0]['_give_recurring'] === 'yes') {
                    $level = $_give_donation_levels[0];
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
     * @unreleased
     */
    protected function getBillingPeriodFromMeta(string $period): SubscriptionPeriod
    {
        return SubscriptionPeriod::isValid($period) ? new SubscriptionPeriod($period) : SubscriptionPeriod::MONTH();
    }
}
