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
 * @since 4.8.0
 */
export type SubscriptionPeriod = 'day' | 'week' | 'month' | 'quarter' | 'year';

/**
 * @since 4.8.0
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
 * @since 4.8.0
 */
export type SubscriptionMode = 'test' | 'live';

/**
 * @since 4.8.0
 */
type Money = {
    value: number;
    currency: string;
};

/**
 * @since 4.8.0
 */
type DateTime = {
    date: string;
    timezone: string;
    timezone_type: number;
};

/**
 * @since 4.8.0
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
