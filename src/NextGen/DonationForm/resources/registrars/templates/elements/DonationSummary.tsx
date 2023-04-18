import {__} from '@wordpress/i18n';
import useCurrencyFormatter from '@givewp/forms/app/hooks/useCurrencyFormatter';
import {isSubscriptionPeriod, SubscriptionPeriod} from '../groups/DonationAmount/subscriptionPeriod';
import {ReactElement, useCallback} from 'react';
import {createInterpolateElement} from '@wordpress/element';

/**
 * @unreleased update subscription frequency label
 * @since 0.1.0
 */
export default function DonationSummary() {
    const {useWatch} = window.givewp.form.hooks;
    const currency = useWatch({name: 'currency'});
    const amount = useWatch({name: 'amount'});
    const period = useWatch({name: 'subscriptionPeriod'});
    const frequency = useWatch({name: 'subscriptionFrequency'});
    const formatter = useCurrencyFormatter(currency, {});

    const givingFrequency = useCallback(() => {
        if (isSubscriptionPeriod(period)) {
            const subscriptionPeriod = new SubscriptionPeriod(period);

            if (frequency > 1) {
                return createInterpolateElement(__('Every <period />', 'give'), {
                    period: <span>{`${frequency} ${subscriptionPeriod.label().plural()}`}</span>,
                });
            }

            return subscriptionPeriod.label().capitalize().adjective();
        }

        return __('One time', 'give');
    }, [period]);

    return (
        <ul className="givewp-elements-donationSummary__list">
            <LineItem label={__('Payment Amount', 'give')} value={formatter.format(Number(amount))} />
            <LineItem label={__('Giving Frequency', 'give')} value={givingFrequency()} />
            <LineItem label={__('Donation Total', 'give')} value={formatter.format(Number(amount))} />
        </ul>
    );
}

/**
 * @since 0.1.0
 */
const LineItem = ({label, value}: { label: string; value: string | ReactElement }) => {
    return (
        <li className="givewp-elements-donationSummary__list-item">
            <div>{label}</div>
            <div>{value}</div>
        </li>
    );
};
