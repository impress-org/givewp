/**
 * WordPress Dependencies
 */
import {__, sprintf} from '@wordpress/i18n';

/**
 * Internal Dependencies
 */
import {GoalInputAttributes} from '@givewp/campaigns/admin/components/CampaignFormModal/types';
import {amountFormatter} from '@givewp/campaigns/utils';

export function getGoalInputAttributes(goalType: string, currency: string): GoalInputAttributes | undefined {
    const currencyFormatter = amountFormatter(currency);

    const attributes: {[selectedGoalType: string]: GoalInputAttributes} = {
        amount: {
            label: __('How much do you want to raise?', 'give'),
            description: __('Set the target amount your campaign should raise.', 'give'),
            placeholder: sprintf(__('eg. %s', 'give'), currencyFormatter.format(2000)),
        },
        donations: {
            label: __('How many donations do you need?', 'give'),
            description: __('Set the target number of donations your campaign should bring in.', 'give'),
            placeholder: __('eg. 100 donations', 'give'),
        },
        donors: {
            label: __('How many donors do you need?', 'give'),
            description: __('Set the target number of donors your campaign should bring in.', 'give'),
            placeholder: __('eg. 100 donors', 'give'),
        },
        amountFromSubscriptions: {
            label: __('How much do you want to raise?', 'give'),
            description: __(
                'Set the target recurring amount your campaign should raise. One-time donations do not count.',
                'give'
            ),
            placeholder: sprintf(__('eg. %s', 'give'), currencyFormatter.format(2000)),
        },
        subscriptions: {
            label: __('How many recurring donations do you need?', 'give'),
            description: __(
                'Set the target number of recurring donations your campaign should bring in. One-time donations do not count.',
                'give'
            ),
            placeholder: __('eg. 100 subscriptions', 'give'),
        },
        donorsFromSubscriptions: {
            label: __('How many recurring donors do you need?', 'give'),
            description: __(
                'Set the target number of recurring donors your campaign should bring in. One-time donations do not count.',
                'give'
            ),
            placeholder: __('eg. 100 subscribers', 'give'),
        },
    };

    return attributes[goalType];
}
