import {FC} from 'react';


export interface Campaign {
    id: number;
    title: string;
    type: string;
    shortDescription: string;
    longDescription: string;
    logo: string;
    image: string;
    goal: number;
}

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
