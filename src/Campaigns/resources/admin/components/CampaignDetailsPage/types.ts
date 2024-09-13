import {FC} from 'react';

export interface GiveCampaignDetails {
    apiRoot: string;
    apiNonce: string;
    adminUrl: string;
    pluginUrl: string;
    campaign: {
        properties: any;
        settings: {
            landingPageUrl: string;
        };
    };
}

export type campaignDetailsTab = {
    id: string;
    title: string;
    content: FC;
};
