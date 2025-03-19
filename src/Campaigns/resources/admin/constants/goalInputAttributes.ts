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
 * Lookup object for all goal input attributes
 *
 * @unreleased
 */
const goalInputAttributesLookup = (currencyFormatter: ReturnType<typeof amountFormatter>) => ({
    amount: {
        label: __('How much do you want to raise?', 'give'),
        description: __('Set the target amount for your campaign to raise.', 'give'),
        help: sprintf(
            __('Your goal progress is measured by the total amount of funds raised e.g. %s of %s raised.', 'give'),
            currencyFormatter.format(500),
            currencyFormatter.format(1000)
        ),
        placeholder: sprintf(__('e.g. %s', 'give'), currencyFormatter.format(2000)),
    },
    donations: {
        label: __('How many donations?', 'give'),
        description: __('Set the target number of donations for your campaign.', 'give'),
        help: __('Your goal progress is measured by the number of donations. e.g. 1 of 5 donations.', 'give'),
        placeholder: __('e.g. 100 donations', 'give'),
    },
    donors: {
        label: __('How many donors?', 'give'),
        description: __('Set the target number of donors for your campaign.', 'give'),
        help: __('Your goal progress is measured by the number of donors. e.g. 10 of 50 donors have given.', 'give'),
        placeholder: __('e.g. 100 donors', 'give'),
    },
    amountFromSubscriptions: {
        label: __('How much do you want to raise?', 'give'),
        description: __(
            'Set the target recurring amount for your campaign to raise. One-time donations do not count.',
            'give'
        ),
        help: __('Only the first donation amount of a recurring donation is counted toward the goal.', 'give'),
        placeholder: sprintf(__('e.g. %s', 'give'), currencyFormatter.format(2000)),
    },
    subscriptions: {
        label: __('How many recurring donations?', 'give'),
        description: __(
            'Set the target number of recurring donations for your campaign. One-time donations do not count.',
            'give'
        ),
        help: __('Only the first donation of a recurring donation is counted toward the goal.', 'give'),
        placeholder: __('e.g. 100 subscriptions', 'give'),
    },
    donorsFromSubscriptions: {
        label: __('How many recurring donors?', 'give'),
        description: __(
            'Set the target number of recurring donors for your campaign. One-time donations do not count.',
            'give'
        ),
        help: __('Only the donors that subscribed to a recurring donation are counted toward the goal.', 'give'),
        placeholder: __('e.g. 100 subscribers', 'give'),
    },
});

/**
 * Type guard to check if a string is a valid GoalType
 *
 * @unreleased
 */
export const isValidGoalType = (goalType: string): goalType is GoalType => {
    const VALID_GOAL_TYPES = Object.keys(goalInputAttributesLookup(amountFormatter('USD')));

    return VALID_GOAL_TYPES.includes(goalType);
};

/**
 * Goal type handler class
 *
 * @unreleased
 */
export class CampaignGoalInputAttributes {
    protected goalType: GoalType;
    protected currencyFormatter: ReturnType<typeof amountFormatter>;

    /**
     * Constructor
     *
     * @unreleased
     */
    constructor(goalType: GoalType, currency: string) {
        if (!isValidGoalType(goalType)) {
            throw new Error(`Invalid goal type: ${goalType}`);
        }

        this.goalType = goalType;
        this.currencyFormatter = amountFormatter(currency);
    }

    /**
     * Get the attributes for this goal type
     *
     * @unreleased
     */
    getAttributes(): GoalInputAttributes {
        const attributes = goalInputAttributesLookup(this.currencyFormatter);
        return attributes[this.goalType];
    }

    /**
     * Get the label for this goal type
     *
     * @unreleased
     */
    getLabel(): string {
        return this.getAttributes().label;
    }

    /**
     * Get the description for this goal type
     *
     * @unreleased
     */
    getDescription(): string {
        return this.getAttributes().description;
    }

    /**
     * Get the placeholder for this goal type
     *
     * @unreleased
     */
    getPlaceholder(): string {
        return this.getAttributes().placeholder;
    }

    /**
     * Get the help text for this goal type
     *
     * @unreleased
     */
    getHelp(): string {
        return this.getAttributes().help;
    }

    /**
     * Check if this goal type is related to currency
     *
     * @unreleased
     */
    isCurrencyType(): boolean {
        return this.goalType === 'amount' || this.goalType === 'amountFromSubscriptions';
    }

    /**
     * Check if this goal type is related to subscriptions
     *
     * @unreleased
     */
    isSubscriptionType(): boolean {
        return [
            'amountFromSubscriptions',
            'subscriptions',
            'donorsFromSubscriptions'
            ,].includes(this.goalType);
    }
}
