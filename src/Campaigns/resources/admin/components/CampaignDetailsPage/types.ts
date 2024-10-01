import {FC} from 'react';

export interface GiveCampaignDetails {
    adminUrl: string;
    currency: string;
}

export type CampaignDetailsTab = {
    id: string;
    title: string;
    content: FC;
    fullwidth?: boolean;
};

export type CampaignInputFields = {
    title: string;
    status: string;
    longDescription?: string;
    goal: number;
    goalType: string;
};
