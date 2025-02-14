import {__} from '@wordpress/i18n';
import {getCampaignOptionsWindowData, amountFormatter} from '@givewp/campaigns/utils';


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
            const {currency} = getCampaignOptionsWindowData()
            const currencyFormatter = amountFormatter(currency);

            return currencyFormatter.format(value);

        default:
            return value;
    }
}
