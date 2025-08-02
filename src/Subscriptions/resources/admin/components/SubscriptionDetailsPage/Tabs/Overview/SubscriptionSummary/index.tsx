import {__} from '@wordpress/i18n';
import {dateI18n} from '@wordpress/date';
import {amountFormatter} from '@givewp/src/Admin/utils';
import Header from '@givewp/src/Admin/components/Header';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import SummaryTable, {SummaryItem} from '@givewp/src/Admin/components/SummaryTable';
import { Donation } from '@givewp/donations/admin/components/types';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
const calculateEndDate = (subscription: any): string | null => {
    if (!subscription) {
        return null;
    }

    if (subscription.installments === 0) {
        return null;
    }

    const startDate = new Date(subscription.createdAt.date);
    const period = subscription.period;
    const frequency = subscription.frequency;
    const installments = subscription.installments;

    const totalPeriods = (installments - 1) * frequency;

    const endDate = new Date(startDate);
    
    switch (period) {
        case 'day':
            endDate.setDate(endDate.getDate() + totalPeriods);
            break;
        case 'week':
            endDate.setDate(endDate.getDate() + (totalPeriods * 7));
            break;
        case 'month':
            endDate.setMonth(endDate.getMonth() + totalPeriods);
            break;
        case 'quarter':
            endDate.setMonth(endDate.getMonth() + (totalPeriods * 3));
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
 * @unreleased
 */
interface SummaryProps {
    subscription: any;
    intendedAmount: number;
    donation: Donation;
    adminUrl: string;
}

/**
 * @unreleased
 */
export default function Summary({subscription, donation, adminUrl, intendedAmount}: SummaryProps) {
    const endDate = calculateEndDate(subscription);
    
    const summaryItems: SummaryItem[] = [
        {
          label: __('Start date', 'give'),
          value: dateI18n('jS M, Y', subscription?.createdAt?.date, undefined),
        },
        {
          label: __('End date', 'give'),
          value: endDate ? dateI18n('jS M, Y', endDate, undefined) : __('Ongoing', 'give'),
        },
        {
          label: __('Donation form', 'give'),
          value: (
            <a className={styles.link} href={`${adminUrl}/edit.php?post_type=give_forms&page=givewp-form-builder&donationFormID=${donation?.formId}&locale=en_US`} target="_blank" rel="noopener noreferrer">
              {donation?.formTitle}
            </a>
          ),
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
                subtitle={__('Additional information about the recurring donation', 'give')}
            />
            <SummaryTable data={summaryItems} />
        </OverviewPanel>
    );
}
