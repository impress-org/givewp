import {FC} from 'react';

export interface GiveCampaignDetails {
    adminUrl: string;
    currency: string;
    isRecurringEnabled: boolean;
    defaultFormId: number;
    donationForms: { id: number; title: string; }[];
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
