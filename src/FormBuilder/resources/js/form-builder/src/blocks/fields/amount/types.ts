import type {subscriptionPeriod} from "@givewp/forms/registrars/templates/groups/DonationAmount/subscriptionPeriod";

export interface DonationAmountAttributes {
    label: string;
    levels: number[];
    descriptions: string[];
    defaultLevel: number;
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
    recurringEnableOneTimeDonations: boolean
}
