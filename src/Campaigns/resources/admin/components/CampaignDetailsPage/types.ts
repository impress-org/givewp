import {FC} from 'react';

export interface GiveCampaignDetails {
    adminUrl: string;
    currency: string;
    isRecurringEnabled: boolean;
    campaignForms: CampaignFormOption[];
}

export type CampaignFormOption = {
    id: number;
    title: string;
};

export type CampaignDetailsTab = {
    id: string;
    title: string;
    content: FC;
    fullwidth?: boolean;
};
