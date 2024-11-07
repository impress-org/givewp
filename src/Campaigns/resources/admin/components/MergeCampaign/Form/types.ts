import {Campaign} from '../../types';

export interface MergeCampaignFormProps {
    isOpen: boolean;
    handleClose: (response?: any) => void;
    apiSettings: {
        apiRoot: string;
        apiNonce: string;
    };
    title: string;
    campaign?: Campaign;
    historyState: {
        selected: string[];
        names: string[];
    };
}

export type MergeCampaignFormInputs = {
    title: string;
};
