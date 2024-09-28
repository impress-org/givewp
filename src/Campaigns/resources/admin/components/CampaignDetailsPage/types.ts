import {FC} from 'react';

export interface Campaign {
    id: number;
    title: string;
    type: string;
    status: string;
    shortDescription: string;
    longDescription: string;
    logo: string;
    image: string;
    goal: number;
}

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
