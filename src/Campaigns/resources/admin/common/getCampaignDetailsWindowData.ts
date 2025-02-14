import type {GiveCampaignDetails} from '@givewp/campaigns/admin/components/CampaignDetailsPage/types';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export default function getCampaignDetailsWindowData(): GiveCampaignDetails {
    return window.GiveCampaignDetails;
}
