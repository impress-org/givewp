import {OptionProps} from '@givewp/form-builder-library/build/OptionsPanel/types';
import type {subscriptionPeriod} from '@givewp/forms/registrars/templates/groups/DonationAmount/subscriptionPeriod';

export interface DonationAmountAttributes {
    label: string;
    levels: OptionProps[];
    descriptionsEnabled: boolean;
    priceOption: string;
    setPrice: number;
    customAmount: boolean;
    customAmountMin: number;
    customAmountMax: number;
    recurringEnabled: boolean;
    recurringBillingInterval: string;
    recurringBillingPeriodOptions: subscriptionPeriod[];
    recurringLengthOfTime: string;
    recurringOptInDefaultBillingPeriod: subscriptionPeriod | 'one-time';
    recurringEnableOneTimeDonations: boolean;
}
