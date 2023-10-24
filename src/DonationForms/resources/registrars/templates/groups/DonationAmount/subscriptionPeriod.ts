import { __ } from "@wordpress/i18n";

/**
 * @since 3.0.0
 */
type periodLabel = {
    singular: string;
    plural: string;
    adjective: string;
};

/**
 * @since 3.0.0
 */
const capitalize = (text: string): string => {
    return text.toLowerCase().replace(/\w/, (firstLetter) => firstLetter.toUpperCase());
};

/**
 * @since 3.0.0
 */
const subscriptionPeriodLabelLookup = {
    day: {
        singular: __('day', 'give'),
        plural: __('days', 'give'),
        adjective: __('daily', 'give'),
    } as periodLabel,
    week: {
        singular: __('week', 'give'),
        plural: __('weeks', 'give'),
        adjective: __('weekly', 'give'),
    } as periodLabel,
    month: {
        singular: __('month', 'give'),
        plural: __('months', 'give'),
        adjective: __('monthly', 'give'),
    } as periodLabel,
    quarter: {
        singular: __('quarter', 'give'),
        plural: __('quarters', 'give'),
        adjective: __('quarterly', 'give'),
    } as periodLabel,
    year: {
        singular: __('year', 'give'),
        plural: __('years', 'give'),
        adjective: __('yearly', 'give'),
    } as periodLabel,
};

/**
 * @since 3.0.0
 */
type subscriptionPeriod = keyof typeof subscriptionPeriodLabelLookup;

/**
 * @since 3.0.0
 */
class SubscriptionPeriod {
    protected period: subscriptionPeriod;

    constructor(period: subscriptionPeriod) {
        if (!isSubscriptionPeriod(period)) {
            throw new Error(`Invalid subscription period: ${period}`);
        }

        this.period = period;
    }

    label(): SubscriptionPeriodLabel {
        const periodLabel = subscriptionPeriodLabelLookup[this.period];

        return new SubscriptionPeriodLabel(periodLabel);
    }
}

/**
 * @since 3.0.0
 */
class SubscriptionPeriodLabel {
    protected periodLabel: periodLabel;
    protected shouldCapitalize: boolean = false;

    constructor(periodLabel: periodLabel) {
        this.periodLabel = periodLabel;
    }

    capitalize(): this {
        this.shouldCapitalize = true;

        return this;
    }

    singular(): string {
        return this.format(this.periodLabel.singular);
    }

    plural(): string {
        return this.format(this.periodLabel.plural);
    }

    adjective(): string {
        return this.format(this.periodLabel.adjective);
    }

    get(frequency?: number): string {
        return frequency > 1 ? `${frequency} ${this.format(this.periodLabel.plural)}` : this.format(this.periodLabel.singular);
    }

    private format(label) {
        return this.shouldCapitalize ? capitalize(label) : label;
    }
}

/**
 * @since 3.0.0
 */
const isSubscriptionPeriod = (period: subscriptionPeriod): period is subscriptionPeriod => {
    return period in subscriptionPeriodLabelLookup;
}

export type {subscriptionPeriod, periodLabel};

export {SubscriptionPeriod, isSubscriptionPeriod};
