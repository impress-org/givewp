type Gateway = {
    enabled: boolean;
    id: string;
    label: string;
    supportsRefund: boolean;
    supportsSubscriptions: boolean;
};

/**
 * @unreleased
 */
export type GiveSubscriptionOptions = {
    isAdmin: boolean;
    adminUrl: string;
    pluginUrl: string;
    apiRoot: string;
    legacyApiRoot: string;
    apiNonce: string;
    syncSubscriptionNonce: string;
    subscriptionsAdminUrl: string;
    currency: string;
    isRecurringEnabled: boolean;
    isFeeRecoveryEnabled: boolean;
    subscriptionStatuses: { [statusCode: string]: string };
    mode: 'test' | 'live';
    gateways: Gateway[];
}
