import {Header, OverviewPanel, SummaryItem, SummaryTable} from '@givewp/admin/components';
import {amountFormatter} from '@givewp/src/Admin/utils';
import {Subscription} from '@givewp/subscriptions/admin/components/types';
import {dateI18n} from '@wordpress/date';
import {__} from '@wordpress/i18n';
import styles from './styles.module.scss';
import {getSubscriptionEmbeds} from '@givewp/subscriptions/common';


/**
 * Calculates the end date of a subscription based on its billing parameters.
 *
 * The calculation uses the formula: End Date = Start Date + (Installments - 1) × Frequency × Period
 *
 * For example:
 * - A monthly subscription with 12 installments: Start Date + 11 months
 * - A weekly subscription with 4 installments: Start Date + 3 weeks
 * - A yearly subscription with 5 installments: Start Date + 4 years
 *
 * @param subscription - The subscription object containing billing parameters
 * @returns ISO string of the calculated end date, or null if calculation is not possible
 *
 * @since 4.8.0
 */
const calculateEndDate = (subscription: Subscription): string | null => {
    if (!subscription) {
        return null;
    }

    // If installments is 0, the subscription is ongoing (no end date)
    if (subscription.installments === 0) {
        return null;
    }

    const startDate = new Date(subscription.createdAt);
    const period = subscription.period; // day, week, month, quarter, year
    const frequency = subscription.frequency; // how many periods between each payment
    const installments = subscription.installments; // total number of payments

    // Calculate total periods to add: (installments - 1) × frequency
    // We subtract 1 from installments because the first payment is on the start date
    const totalPeriods = (installments - 1) * frequency;

    const endDate = new Date(startDate);

    // Add the calculated periods based on the billing period type
    switch (period) {
        case 'day':
            endDate.setDate(endDate.getDate() + totalPeriods);
            break;
        case 'week':
            endDate.setDate(endDate.getDate() + totalPeriods * 7);
            break;
        case 'month':
            endDate.setMonth(endDate.getMonth() + totalPeriods);
            break;
        case 'quarter':
            endDate.setMonth(endDate.getMonth() + totalPeriods * 3);
            break;
        case 'year':
            endDate.setFullYear(endDate.getFullYear() + totalPeriods);
            break;
        default:
            return null;
    }

    return endDate.toISOString();
};

/**
 * @since 4.8.0
 */
interface SummaryProps {
    subscription: Subscription;
    intendedAmount: number;
    adminUrl: string;
    isLoading: boolean;
}

/**
 * @since 4.10.0 removed donation from props
 * @since 4.8.0
 */
export default function Summary({subscription, adminUrl, intendedAmount, isLoading}: SummaryProps) {
    const {form} = getSubscriptionEmbeds(subscription);
    const formTitle = form?.title ?? __('Donation Form', 'give');
    const endDate = calculateEndDate(subscription);

    const summaryItems: SummaryItem[] = [
        {
            label: __('Start date', 'give'),
            value: dateI18n('jS M, Y', subscription?.createdAt, undefined),
        },
        {
            label: __('End date', 'give'),
            value: endDate ? dateI18n('jS M, Y', endDate, undefined) : __('Ongoing', 'give'),
        },
        {
            label: __('Donation form', 'give'),
            value: subscription?.donationFormId ? (
                <a
                    className={styles.link}
                    href={`${adminUrl}edit.php?post_type=give_forms&page=givewp-form-builder&donationFormID=${subscription?.donationFormId}`}
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {formTitle}
                </a>
            ) : '',
        },
        {
            label: __('Renewal', 'give'),
            value: amountFormatter(subscription?.amount?.currency).format(intendedAmount),
        },
    ];

    return (
        <OverviewPanel className={styles.summaryPanel}>
            <Header
                title={__('Summary', 'give')}
                subtitle={__('Information about the initial recurring donation', 'give')}
            />
            <SummaryTable data={summaryItems} isLoading={isLoading} />
        </OverviewPanel>
    );
}
