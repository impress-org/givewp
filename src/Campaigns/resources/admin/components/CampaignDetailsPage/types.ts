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
