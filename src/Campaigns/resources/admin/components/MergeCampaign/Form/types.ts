export interface MergeCampaignModalProps {
    isOpen: boolean;
    setOpen?: (isOpen?: boolean) => void;
    onMerged?: () => void;
    campaigns: {
        selected: (string | number)[];
        names: string[];
    };
}

export interface MergeCampaignFormProps {
    isOpen: boolean;
    handleClose: (response?: any) => void;
    onMerged?: () => void;
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
