export interface GiveCampaignDetails {
    apiRoot: string;
    apiNonce: string;
    adminUrl: string;
    pluginUrl: string;
    campaignDetailsPage: {
        overviewTab: any;
        settingsTab: {
            landingPageUrl: string;
        };
    };
}
