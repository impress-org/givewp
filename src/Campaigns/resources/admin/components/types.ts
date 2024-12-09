export type Campaign = {
    id?: number;
    type: string;
    title: string;
    shortDescription: string;
    longDescription: string;
    logo: string;
    image: string;
    primaryColor: string;
    secondaryColor: string;
    goalType: string;
    goal: number;
    goalProgress: number;
    status: string;
    startDateTime: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    endDateTime: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    createdAt: string;
    //updatedAt: string;
    enableCampaignPage: boolean;
    defaultFormId: number;
    defaultFormTitle: string;
};

/*export interface Campaign {
    id: number;
    title: string;
    type: string;
    status: string;
    shortDescription: string;
    longDescription: string;
    logo: string;
    image: string;
    goal: number;
}*/
