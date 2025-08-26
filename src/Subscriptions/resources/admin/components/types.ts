import {Donor} from '@givewp/donors/admin/components/types';
import {Donation} from '@givewp/donations/admin/components/types';

type PaymentGateway = {
    id: string;
    name: string;
    label: string;
    subscriptionUrl: string;
    canSync: boolean;
}

/**
 * @unreleased
 */
export type SubscriptionPeriod = 'day' | 'week' | 'month' | 'quarter' | 'year';

/**
 * @unreleased
 */
export type SubscriptionStatus =
    | 'pending'
    | 'active'
    | 'expired'
    | 'completed'
    | 'failing'
    | 'cancelled'
    | 'suspended'
    | 'paused'
    | 'trashed';

/**
 * @unreleased
 */
export type SubscriptionMode = 'test' | 'live';

/**
 * @unreleased
 */
type Money = {
    value: number;
    currency: string;
};

/**
 * @unreleased
 */
type DateTime = {
    date: string;
    timezone: string;
    timezone_type: number;
};

/**
 * @unreleased
 */
export type Subscription = {
    id: number;
    donationFormId: number;
    createdAt: DateTime;
    renewsAt: DateTime;
    donorId: number;
    period: SubscriptionPeriod;
    frequency: number;
    installments: number;
    transactionId: string;
    mode: SubscriptionMode;
    amount: Money;
    feeAmountRecovered: Money;
    status: SubscriptionStatus;
    gatewaySubscriptionId: string;
    gatewayId: string;
    donor?: Donor;
    donations?: Donation[];
    gateway?: PaymentGateway;
    projectedAnnualRevenue?: Money;
};
