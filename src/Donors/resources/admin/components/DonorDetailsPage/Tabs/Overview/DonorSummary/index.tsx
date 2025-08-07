import {__} from '@wordpress/i18n';
import {dateI18n} from '@wordpress/date';
import {amountFormatter, getRelativeTimeString} from '@givewp/src/Admin/utils';
import {useDonorStatistics} from '@givewp/donors/hooks/useDonorStatistics';
import {getDonorOptionsWindowData, useDonorEntityRecord} from '@givewp/donors/utils';
import {Header, OverviewPanel, SummaryTable, type SummaryItem} from '@givewp/admin/components';
import styles from "./styles.module.scss";

/**
 * @since 4.5.0
 */
interface SummaryProps {
    donorId: number;
}

/**
 * @since 4.5.0
 */
export default function Summary({donorId}: SummaryProps) {
    const {currency, mode} = getDonorOptionsWindowData();
    const {statistics: stats, isResolving: statsLoading, hasResolved: statsResolved} = useDonorStatistics(donorId, mode);
    const {record: donor} = useDonorEntityRecord(donorId);

    const summaryItems: SummaryItem[] = !stats
        ? []
        : [
              {
                  label: __('Donor Since', 'give'),
                  value: dateI18n('M j, Y', donor?.createdAt.date, undefined),
              },
              {
                  label: __('Last Contributed', 'give'),
                  value: stats.donations.last
                      ? getRelativeTimeString(new Date(stats.donations.last.date))
                      : __('Never', 'give'),
              },
              {
                  label: __('First Contribution', 'give'),
                  value: stats.donations.first
                      ? (
                          <div className={styles.summaryTableValues}>
                            <p>{amountFormatter(currency).format(parseFloat(stats.donations.first.amount))}</p>
                            <p>{dateI18n('M j, Y', stats.donations.first.date, undefined)}</p>
                          </div>
                        )
                      : __('None', 'give'),
              },
              {
                  label: __('Donor Type', 'give'),
                  value: stats.donorType,
                  isPill: true,
              },
              {
                  label: __('Total Donations', 'give'),
                  value: stats.donations.count?.toString() ?? '0',
              },
              {
                  label: __('Preferred Method', 'give'),
                  value: stats.preferredPaymentMethod || __('None', 'give'),
              },
          ];

    return (
        <OverviewPanel className={styles.summaryPanel}>
            <Header
                title={__('Summary', 'give')}
                subtitle={__('Additional information about the donor', 'give')}
            />
            <SummaryTable data={summaryItems} />
        </OverviewPanel>
    );
}
