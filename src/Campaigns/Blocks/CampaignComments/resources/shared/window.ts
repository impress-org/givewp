export type commentData = {
    comment: string;
    date: string;
    campaignTitle: string;
    donorName: string;
    avatar: string;
    anonymous: boolean;
};

export type GiveCampaignCommentsBlockWindowData = commentData[];

declare const window: {
    GiveCampaignCommentsBlockWindowData: GiveCampaignCommentsBlockWindowData;
} & Window;

export default function getGiveCampaignCommentsBlockWindowData(): GiveCampaignCommentsBlockWindowData {
    return window.GiveCampaignCommentsBlockWindowData;
}
