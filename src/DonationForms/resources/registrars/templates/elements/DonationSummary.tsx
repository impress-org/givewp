import {useMemo} from 'react';
import {__} from '@wordpress/i18n';
import {isSubscriptionPeriod, SubscriptionPeriod} from '../groups/DonationAmount/subscriptionPeriod';
import {createInterpolateElement} from '@wordpress/element';

/**
 * @since 3.0.0
 */
export default function DonationSummary() {
    const DonationSummaryItemsTemplate = window.givewp.form.templates.layouts.donationSummaryItems;
    const { useCurrencyFormatter, useDonationSummary } = window.givewp.form.hooks;
    const { items } = useDonationSummary();
    const {state: {
        currency,
        donationAmount,
        donationAmountTotal,
        subscriptionPeriod: period,
        subscriptionFrequency: frequency,
    }} = window.givewp.form.hooks.useDonationSummary();
    const formatter = useCurrencyFormatter(currency);

    const givingFrequency = useMemo(() => {
        if (isSubscriptionPeriod(period)) {
            const subscriptionPeriod = new SubscriptionPeriod(period);

            if (frequency > 1) {
                return createInterpolateElement(__('Every <period />', 'give'), {
                    period: <span>{`${frequency} ${subscriptionPeriod.label().plural()}`}</span>
                });
            }

            return subscriptionPeriod.label().capitalize().adjective();
        }

        return __('One time', 'give');
    }, [period, frequency]);

    const amountItem = {
        id: 'amount',
        label: __('Payment Amount', 'give'),
        value: formatter.format(donationAmount)
    };

    const frequencyItem = {
        id: 'frequency',
        label: __('Giving Frequency', 'give'),
        value: givingFrequency
    };

    const donationSummaryItems = [amountItem, frequencyItem, ...Object.values(items)];

    const donationTotal = formatter.format(donationAmountTotal);

    return (
        <>
            <h2 className="givewp-elements-donationSummary__header">{__('Donation Summary', 'give')}</h2>
            <DonationSummaryItemsTemplate items={donationSummaryItems} total={donationTotal} />
        </>
    );
}
