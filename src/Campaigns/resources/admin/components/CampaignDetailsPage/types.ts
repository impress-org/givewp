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

export type CampaignDetailsTab = {
    id: string;
    title: string;
    content: FC;
};

export type CampaignDetailsInputs = {
    title: string;
};
