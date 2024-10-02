import {FC} from 'react';

export interface GiveCampaignDetails {
    apiRoot: string;
    apiNonce: string;
    adminUrl: string;
    currency: string;
    pluginUrl: string;
    campaign: {
        goalProgress: number;
    };
}

export type CampaignDetailsTab = {
    id: string;
    title: string;
    content: FC;
};

export type CampaignInputFields = {
    title: string;
    status: string;
    longDescription?: string;
    goal: number;
    goalType: string;
};
