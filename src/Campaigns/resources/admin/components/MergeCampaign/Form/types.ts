export interface MergeCampaignModalProps {
    isOpen: boolean;
    setOpen: (response?: any) => void;
    campaigns: {
        selected: (string | number)[];
        names: string[];
    };
}

export interface MergeCampaignFormProps {
    isOpen: boolean;
    handleClose: (response?: any) => void;
    title: string;
    campaigns: {
        selected: (string | number)[];
        names: string[];
    };
}

export type MergeCampaignFormInputs = {
    title: string;
    destinationCampaignId: string;
};
