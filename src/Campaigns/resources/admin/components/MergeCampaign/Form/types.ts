export interface MergeCampaignFormProps {
    isOpen: boolean;
    handleClose: (response?: any) => void;
    title: string;
    campaigns: {
        selected: string[];
        names: string[];
    };
}

export type MergeCampaignFormInputs = {
    title: string;
    destinationCampaign: string;
};
