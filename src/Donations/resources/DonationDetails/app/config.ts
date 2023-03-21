import {__} from '@wordpress/i18n';
import {DataValues} from './components/FormTemplate/types';

declare global {
    interface Window {
        GiveDonations: {
            apiNonce: string;
            apiRoot: string;
            adminUrl: string;
            donationDetails?: DataValues;
        };
    }
}

export const {donationDetails: data, apiRoot: endpoint} = window.GiveDonations;

export const defaultFormValues: {
    amount: string;
    feeAmountRecovered: string;
    createdAt: string;
    status: string;
    form: number;
} = {
    amount: data.amount.value ?? '0',
    feeAmountRecovered: data.feeAmountRecovered ?? '0',
    createdAt: data.createdAt.date,
    status: data.status,
    form: data.formId,
};

export const pageInformation: {
    id: number;
    description: string;
    title: string;
} = {
    id: data.id,
    description: __('Donation ID', 'give'),
    title: __('Donation', 'give'),
};

export const actionConfig: Array<{title: string; action: () => void}> = [
    {title: __('Refund donation', 'give'), action: () => alert('refund donation')},
    {title: __('Download receipt', 'give'), action: () => alert('Download receipt')},
    {title: __('Resend receipt', 'give'), action: () => alert('Resend receipt')},
    {title: __('Delete donation', 'give'), action: () => alert('Delete donation')},
];

export const donationStatusOptions: Array<{value: string; label: string}> = [
    {
        value: 'publish',
        label: __('Completed', 'give'),
    },
    {
        value: 'pending',
        label: __('Pending', 'give'),
    },
    {
        value: 'processing',
        label: __('Processing', 'give'),
    },
    {
        value: 'refunded',
        label: __('Refunded', 'give'),
    },
    {
        value: 'revoked',
        label: __('Revoked', 'give'),
    },
    {
        value: 'failed',
        label: __('Failed', 'give'),
    },
    {
        value: 'cancelled',
        label: __('Cancelled', 'give'),
    },
    {
        value: 'abandoned',
        label: __('Abandoned', 'give'),
    },
    {
        value: 'preApproval',
        label: __('Pre-approved', 'give'),
    },
];

//TODO: temporary data - will be replaced
export const donationFormOptions = [
    {value: 1, label: 'donation form 1'},
    {value: 2, label: 'donation form 2'},
    {value: 3, label: 'donation form 3'},
    {value: 4, label: 'donation form 4'},
];

//TODO: temporary data - will be replaced with data fetching
export const navigationalOptions = [
    {id: 1, title: 'donation 1'},
    {id: 2, title: 'donation 2'},
    {id: 3, title: 'donation 3'},
];
