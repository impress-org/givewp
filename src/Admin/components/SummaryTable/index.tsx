import React from 'react';
import classnames from 'classnames';
import Spinner from '@givewp/src/Admin/components/Spinner';
import styles from './styles.module.scss';

/**
 * @since 4.8.0 abstracted from DonorSummary to be used with other components. Add ReactNode support.
 * @since 4.5.0
 */
export type SummaryItem = {
  label: string;
  value: string | React.ReactNode;
  isPill?: boolean;
};

/**
 * @since 4.8.0
 */
interface SummaryTableProps {
  title?: string;
  subtitle?: string;
  data: SummaryItem[];
  isLoading?: boolean;
}

/**
 * @since 4.8.0
 */
export default function SummaryTable({data, isLoading}: SummaryTableProps) {
  return (
    <div className={styles.summaryTableContainer}>
        {data.map((item, index) => (
            <div className={styles.summaryTable} key={index}>
                <p className={styles.summaryTableLabel}>{item.label}</p>
                {isLoading ? (
                  <Spinner />
                ) : (
                  <strong className={classnames(styles.summaryTableValue, {
                    [styles.pill]: item.isPill,
                  })}>
                    {item.value}
                  </strong>
                )}
            </div>
        ))}
    </div>
  );
}

