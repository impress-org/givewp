import React from 'react';
import { __ } from '@wordpress/i18n';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
export default function DonationBreakdown() {
  return (
    <>
      <Row label={__("Donation amount", 'give')} value="$270.00" underline/>
      <Row
        label={
          <div className={styles.eventLabel}>
            {__("Standard (x1)", 'give')}
            <a href="#" className={styles.eventLink}>{__("Event name", 'give')}</a>
          </div>
        }
        value="$30.00"
        underline
      />
      <Row label={__("Total", 'give')} value="$300.00" isTotal />
      <Row link={{ href: '#', label: __('Close', 'give')}}/>
      <Row label={__("Exchange rate", 'give')} value="1.14" />
      <Row label={__("Base currency", 'give')} value={__("Euro (€)", 'give')} />
      <Row label={__("Base total", 'give')} value="€264.14" />
    </>
  );
}

/**
 * @unreleased
 */
type RowProps = {
  label?: React.ReactNode;
  value?: React.ReactNode;
  isTotal?: boolean;
  children?: React.ReactNode;
  link?: { href: string; label: string;};
  underline?: boolean;
};

/**
 * @unreleased
 */
function Row({ label, value, isTotal = false, children, link }: RowProps) {
  return (
    <div className={`${styles.row} ${isTotal ? styles.rowTotal : ''}`}>
      <dt className={styles.label}>
          {label}
          {children}
      </dt>
      <dd className={styles.value}>
          {isTotal ? <strong>{value}</strong> : value}
          {link && (
            <a
              href={link.href}
              className={styles.link}
            >
              {link.label}
            </a>
          )}
      </dd>
    </div>
  );
}