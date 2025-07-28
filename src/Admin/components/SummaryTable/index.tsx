import React from 'react';
import classnames from 'classnames';
import Header from '@givewp/src/Admin/components/Header';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import styles from './styles.module.scss';

/**
 * @unreleased abstracted from DonorSummary to be used with other components. Add ReactNode support.
 * @since 4.5.0
 */
export type SummaryItem = {
  label: string;
  value:
    | string
    | {
        value1: string;
        value2: string;
      }
    | React.ReactNode;
  isPill?: boolean;
};

/**
 * @unreleased
 */
interface SummaryTableProps {
  title?: string;
  subtitle?: string;
  data: SummaryItem[];
}

/**
 * @unreleased
 */
export default function SummaryTable({data,}: SummaryTableProps) {
  return (
    <div className={styles.summaryTableContainer}>
        {data.map((item, index) => (
            <div className={styles.summaryTable} key={index}>
                <p className={styles.summaryTableLabel}>{item.label}</p>
                {renderValue(item.value, item.isPill)}
            </div>
        ))}
    </div>
  );
}

/**
 * @unreleased
 */
  const renderValue = (value: SummaryItem['value'], isPill?: boolean) => {
    const isObjectWithValues = typeof value === 'object' && value !== null && 'value1' in value && 'value2' in value;
    const isSingleValue = React.isValidElement(value) || typeof value === 'string';

    if (isObjectWithValues) {
      return (
        <div className={styles.summaryTableValues}>
          <p>{value.value1}</p>
          <p>{value.value2}</p>
        </div>
      );
    }
    
    return (
      <strong className={classnames(styles.summaryTableValue, {
        [styles.pill]: isPill,
      })}>
        {isSingleValue && value}
      </strong>
    );
  };