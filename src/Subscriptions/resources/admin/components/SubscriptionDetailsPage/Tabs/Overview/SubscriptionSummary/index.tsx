import {__} from '@wordpress/i18n';
import {dateI18n} from '@wordpress/date';
import {amountFormatter} from '@givewp/src/Admin/utils';
import Header from '@givewp/src/Admin/components/Header';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import SummaryTable, {SummaryItem} from '@givewp/src/Admin/components/SummaryTable';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
interface SummaryProps {
    subscription: any;
    intendedAmount: number;
}

/**
 * @unreleased
 */
export default function Summary({subscription, intendedAmount}: SummaryProps) {
    const summaryItems: SummaryItem[] = [
        {
          label: __('Start date', 'give'),
          value: dateI18n('jS M, Y', subscription?.createdAt?.date, undefined),
        },
        {
          label: __('End date', 'give'),
          value: dateI18n('jS M, Y', '2025-07-24 20:21:19.000000', undefined),
        },
        {
          label: __('Donation form', 'give'),
          value: (
            <a className={styles.link} href={''} target="_blank" rel="noopener noreferrer">
              {'Giving 4 Good!'}
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
