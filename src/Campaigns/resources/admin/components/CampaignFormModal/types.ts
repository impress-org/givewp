import {Campaign} from '../types';

export interface CampaignModalProps {
    isOpen: boolean;
    handleClose: (response?: any) => void;
    apiSettings: {
        apiRoot: string;
        apiNonce: string;
    };
}

export type CampaignFormInputs = {
    title: string;
    shortDescription: string;
    image: string;
    goalType: string;
    goal: number;
    startDateTime: string;
    endDateTime: string;
};

export type GoalInputAttributes = {
    label: string;
    description: string;
    help: string;
    placeholder: string;
};

export type GoalTypeOption = {
    type: string;
    label: string;
    description: string;
    selected: boolean;
    register: any;
};
