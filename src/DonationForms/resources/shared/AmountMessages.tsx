import {createInterpolateElement} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import {subscriptionPeriod, SubscriptionPeriod} from "../registrars/templates/groups/DonationAmount/subscriptionPeriod";

/**
 * Returns the donor-facing message for a fixed amount donation.
 *
 * @since 3.0.0
 */
export function OneTimeAmountMessage({amount}: {amount: string}) {
    return createInterpolateElement(__('This donation is <amount/>', 'give'), {
        amount: <strong>{amount}</strong>,
    });
}

/**
 * Returns the donor-facing message for a recurring donation.
 *
 * @since 3.0.0
 */
export function RecurringAmountMessage({
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
        : __('This donation <amount /> every <period /> for <count /> <donations />.', 'give');

    return createInterpolateElement(translatableString, {
        amount: isFixedAmount ? (
            <span>
                {__('is', 'give')} <strong>{fixedAmount}</strong>
            </span>
        ) : (
            <span>{__('repeats', 'give')}</span>
        ),
        period: <strong>{subscriptionPeriod.label().get(frequency)}</strong>,
        count: <strong>{installments}</strong>,
        donations: <strong>{__('donations', 'give')}</strong>,
    });
}
