import {__} from '@wordpress/i18n';

export const getGoalDescription = (goalType: string) => {
    switch (goalType) {
        case 'amount':
            return __('Amount raised', 'give');
        case 'donations':
            return __('Number of donations', 'give');
        case 'donors':
            return __('Number of donors', 'give');
        case 'amountFromSubscriptions':
            return __('Recurring amount raised', 'give');
        case 'subscriptions':
            return __('Number of recurring donations', 'give');
        case 'donorsFromSubscriptions':
            return __('Number of recurring donors', 'give');
    }
}


export const getGoalFormattedValue = (goalType: string, value: number) => {
    switch (goalType) {
        case 'amount':
        case 'amountFromSubscriptions':

        default:
            return value;
    }
}
