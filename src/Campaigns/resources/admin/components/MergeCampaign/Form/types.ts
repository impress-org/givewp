export interface MergeCampaignModalProps {
    isOpen: boolean;
    setOpen?: (isOpen?: boolean) => void;
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
