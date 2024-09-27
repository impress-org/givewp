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
        goalProgress: number;
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
