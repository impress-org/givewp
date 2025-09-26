export interface CampaignFormProps {
    campaignId: number;
    formId: number;
    campaignsWithForms: {
        [campaignId: string]: {
            title: string;
            defaultFormId: string;
            forms: {
                [formId: string]: string;
            };
        };
    };
}
