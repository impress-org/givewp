import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
export default function DonationBreakdown() {
  return (
    <div className={styles.rowContainer}>
      <Row className={styles.donationRow} label={__("Donation amount", 'give')} value="$270.00" />
      <Row className={styles.donationRow} label={<EventLabel />} value="$30.00" />
      <Row label={<strong>{__("Total", 'give')}</strong>} value={<strong>$300.00</strong>} />
      <a href="#" className={styles.close}>{__('Close', 'give')}</a>
      <Row className={styles.currencyRow} label={__("Exchange rate", 'give')} value="1.14" />
      <Row className={styles.currencyRow} label={__("Base currency", 'give')} value={__("Euro (€)", 'give')} />
      <Row className={styles.currencyRow} label={__("Base total", 'give')} value="€264.14" />
    </div>
  );
}

/**
 * @unreleased
 */
type RowProps = {
  label?: React.ReactNode;
  value?: React.ReactNode;
  children?: React.ReactNode;
  className?: string;
};

/**
 * @unreleased
 */
function Row({ label, value, children, className }: RowProps) {
  return (
    <div className={classnames(styles.row, className)}>
      <dt className={styles.label}>
          {label}
          {children}
      </dt>
      <dd className={styles.value}>
          {value}
      </dd>
    </div>
  );
}

function EventLabel() {
  return (
    <div className={styles.eventLabel}>
      <p>{__("Standard (x1)", 'give')}</p>
      <a href="#" className={styles.eventLink}>{__("Event name", 'give')}</a>
    </div>
  )
}