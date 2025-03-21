export interface GiveCampaignsListTable {
    apiNonce: string;
    apiRoot: string;
    table: {columns: Array<object>};
    adminUrl: string;
    pluginUrl: string;
    currency: string;
    isRecurringEnabled: boolean;
}
