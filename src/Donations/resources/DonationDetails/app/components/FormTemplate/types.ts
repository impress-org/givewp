/**
 *
 * @unreleased
 */

interface BillingAddress {
    country: string | null;
    address1: string | null;
    address2: string | null;
    city: string | null;
    state: string | null;
    zip: string | null;
}

/**
 *
 * @unreleased
 */

export interface Amount {
    currency: string;
    value: string;
}

/**
 *
 * @unreleased
 */

export interface DateTime {
    date: string;
    timezone_type: number;
    timezone: string;
}

/**
 *
 * @unreleased
 */

export interface DataValues {
    id: number;
    formId: number;
    formTitle: string;
    purchaseKey: string;
    donorIp: string;
    createdAt: DateTime;
    updatedAt: DateTime;
    status: string;
    type: string;
    mode: string;
    amount: Amount;
    feeAmountRecovered: null | string;
    exchangeRate: null | number;
    gatewayId: string;
    donorId: number;
    firstName: string;
    lastName: string;
    email: string;
    subscriptionId: number;
    billingAddress: BillingAddress;
    anonymous: boolean;
    levelId: string;
    gatewayTransactionId: null | string;
    company: null | string;
    gatewayLabel: string;
}
