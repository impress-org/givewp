import {createInterpolateElement} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import type {subscriptionPeriod} from './subscriptionPeriod';
import {SubscriptionPeriod} from './subscriptionPeriod';

/**
 * @unreleased
 */
export default function FixedAmountRecurringMessage({
    isFixedAmount,
    fixedAmount,
    period,
    frequency,
    installments,
}: {
    isFixedAmount: boolean;
    fixedAmount: string;
    period: subscriptionPeriod;
    frequency: number;
    installments: number;
}) {
    const subscriptionPeriod = new SubscriptionPeriod(period);

    const translatableString = !installments
        ? __('This donation <amount /> every <period />.', 'give')
        : __('This donation <amount /> every <period /> for <count /> <payments />.', 'give');

    const message = createInterpolateElement(translatableString, {
        amount: isFixedAmount ? (
            <span>
                {__('is', 'give')} <strong>{fixedAmount}</strong>
            </span>
        ) : (
            <span>{__('occurs', 'give')}</span>
        ),
        period: <strong>{subscriptionPeriod.label().get(frequency)}</strong>,
        count: <strong>{installments}</strong>,
        payments: <strong>{__('payments', 'give')}</strong>,
    });

    return <div className="givewp-fields-amount__fixed-message">{message}</div>;
}