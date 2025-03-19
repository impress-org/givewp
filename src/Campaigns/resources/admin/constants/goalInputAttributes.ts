/**
 * WordPress Dependencies
 */
import {__, sprintf} from '@wordpress/i18n';

/**
 * Internal Dependencies
 */
import type {GoalInputAttributes} from '@givewp/campaigns/admin/components/CampaignFormModal/types';
import type {GoalType} from '@givewp/campaigns/types';
import {amountFormatter} from '@givewp/campaigns/utils';

/**
 * @unreleased
 */
export function getGoalInputAttributes(goalType: GoalType, currency: string): GoalInputAttributes | undefined {
    const currencyFormatter = amountFormatter(currency);

    const attributes: {[selectedGoalType in GoalType]: GoalInputAttributes} = {
        amount: {
            label: __('How much do you want to raise?', 'give'),
            description: __('Set the target amount for your campaign to raise.', 'give'),
            placeholder: sprintf(__('e.g. %s', 'give'), currencyFormatter.format(2000)),
        },
        donations: {
            label: __('How many donations?', 'give'),
            description: __('Set the target number of donations for your campaign.', 'give'),
            placeholder: __('e.g. 100 donations', 'give'),
        },
        donors: {
            label: __('How many donors?', 'give'),
            description: __('Set the target number of donors for your campaign.', 'give'),
            placeholder: __('e.g. 100 donors', 'give'),
        },
        amountFromSubscriptions: {
            label: __('How much do you want to raise?', 'give'),
            description: __(
                'Set the target recurring amount for your campaign to raise. One-time donations do not count.',
                'give'
            ),
            placeholder: sprintf(__('eg. %s', 'give'), currencyFormatter.format(2000)),
        },
        subscriptions: {
            label: __('How many recurring donations?', 'give'),
            description: __(
                'Set the target number of recurring donations for your campaign. One-time donations do not count.',
                'give'
            ),
            placeholder: __('e.g. 100 subscriptions', 'give'),
        },
        donorsFromSubscriptions: {
            label: __('How many recurring donors?', 'give'),
            description: __(
                'Set the target number of recurring donors for your campaign. One-time donations do not count.',
                'give'
            ),
            placeholder: __('e.g. 100 subscribers', 'give'),
        },
    };

    return attributes[goalType];
}
