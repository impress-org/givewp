<?php

namespace Give\FormMigration\Steps;

use Give\FormBuilder\BlockModels\DonationAmountBlockModel;
use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

class RecurringDonationOptions extends FormMigrationStep
{
    public function process()
    {
        /** @var DonationAmountBlockModel $amountBlock */
        $amountBlock = $this->fieldBlocks->findByName('givewp/donation-amount');

        // Recurring Donations = 'no', 'yes_donor', 'yes_admin'
        $_give_recurring = $this->getMetaV2('_give_recurring');

        if ('no' === $_give_recurring) {
            $amountBlock->setRecurringEnabled(false);
        } elseif ('yes_donor' === $_give_recurring) {
            $amountBlock->setRecurringEnabled();
            $amountBlock->setRecurringEnableOneTimeDonations();
        } elseif ('yes_admin' === $_give_recurring) {
            $amountBlock->setRecurringEnabled();
            $amountBlock->setRecurringEnableOneTimeDonations(false);
        } else {
            return;
        }

        // 'donors_choice', 'admin_choice', 'custom' (The "Donor's Choice" option allows the donor to select the time period (commonly also referred as the "frequency") of their subscription. The "Preset Period" option provides only the selected period for the donor's subscription.)
        $_give_period_functionality = $this->getMetaV2( '_give_period_functionality');
        // 'day', 'week', 'month', 'year'
        $_give_period = $this->getMetaV2(  '_give_period');
        // 'day', 'week', 'month', 'year'
        $_give_period_default_donor_choice = $this->getMetaV2('_give_period_default_donor_choice');
        // integer
        $_give_times = $this->getMetaV2('_give_times');
        // integer
        $_give_period_interval = $this->getMetaV2('_give_period_interval');
        // 'yes', 'no'
        $_give_checkbox_default = $this->getMetaV2( '_give_checkbox_default');
          // 'multi', 'set'
        $_give_price_option = $this->getMetaV2( '_give_price_option');

        if ($_give_period_functionality === 'donors_choice') {
            $amountBlock->setRecurringBillingPeriodOptions(...SubscriptionPeriod::values());

            if ($_give_checkbox_default === 'yes'){
                $defaultPeriod = SubscriptionPeriod::isValid($_give_period_default_donor_choice) ? new SubscriptionPeriod($_give_period_default_donor_choice) : SubscriptionPeriod::MONTH();
                $amountBlock->setRecurringOptInDefaultBillingPeriod($defaultPeriod);
            }
        } elseif ($_give_period_functionality === 'admin_choice' && SubscriptionPeriod::isValid($_give_period)) {
            $amountBlock->setRecurringBillingPeriodOptions(new SubscriptionPeriod($_give_period));
        }

        if (!empty($_give_period_interval)){
            $amountBlock->setRecurringBillingInterval((int)$_give_period_interval);
        }

        if (!empty($_give_times)){
            $amountBlock->setRecurringLengthOfTime((int)$_give_times);
        }
    }
}
