import { __ } from "@wordpress/i18n";
import { createInterpolateElement } from '@wordpress/element';
import classnames from 'classnames';
import { CheckCircleIcon, HandHeartIcon, XCircleIcon } from "./icons";
import styles from "./styles.module.scss";

/**
 * Utility function to capitalize the first letter of a string
 * @unreleased
 * @param str - The string to capitalize
 * @returns The string with first letter capitalized
 */
const capitalizeFirstLetter = (str: string): string => {
  if (!str) return str;
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

/**
 * @unreleased
 */
type SyncDetailsProps = {
    isAccurate: boolean;
    platform: string;
    gateway: string;
  }
  
/**
 * @unreleased
 */
export default function SyncDetails({ isAccurate, platform, gateway }: SyncDetailsProps) {
  if(isAccurate) {
    return (
      <div className={styles.accurateDetailWrapper}>
        <div className={styles.detailItem}>
          <p className={styles.detailLabel}>{__('Platform', 'give')}</p>
          <p className={styles.detailValue}>{platform ? capitalizeFirstLetter(platform) : __('Completed', 'give')}</p>
        </div>
        <CheckCircleIcon />
        <div className={styles.detailItem}>
          <p className={styles.detailLabel}>{__('Gateway', 'give')}</p>
          <p className={styles.detailValue}>{gateway ? capitalizeFirstLetter(gateway) : __('Completed', 'give')}</p>
        </div>
      </div>
    )
  }

  return (
    <div className={styles.syncDetails}>
        <div className={styles.detail}>
          <div className={styles.detailContainer}>
            <p className={styles.detailTitle}>{__('OLD', 'give')}</p>
            <div className={styles.detailWrapper}>
              <div className={styles.detailItem}>
                  <p className={styles.detailLabel}>{__('Platform', 'give')}</p>
                  <p className={styles.detailValue}>{capitalizeFirstLetter(platform)}</p>
                </div>
                <XCircleIcon />
                <div className={styles.detailItem}>
                  <p className={styles.detailLabel}>{__('Gateway', 'give')}</p>
                  <p className={styles.detailValue}>{capitalizeFirstLetter(gateway)}</p>
              </div>
            </div>
          </div>
          <div className={styles.detailContainer}>
            <p className={styles.detailTitle}>{__('NEW', 'give')}</p>
            <div className={styles.detailWrapper}>
                <div className={styles.detailItem}>
                  <p className={styles.detailLabel}>{__('Platform', 'give')}</p>
                  <p className={styles.detailValue}>{capitalizeFirstLetter(gateway)}</p>
                </div>
                <CheckCircleIcon />
                <div className={styles.detailItem}>
                  <p className={styles.detailLabel}>{__('Gateway', 'give')}</p>
                  <p className={styles.detailValue}>{capitalizeFirstLetter(gateway)}</p>
                </div>
              </div>
            </div>
        </div>
    </div>
  );
} 

/**
 * @unreleased
 */
type SyncPaymentDetailsProps = {

  payment: {
    gatewayTransactionId: string;
    id: number;
    amount: string;
    createdAt: string;
    status: string;
    type: string;
  } | null;
  isAccurate?: boolean;
  platform?: string;
  gateway?: string;
}

/**
 * @unreleased
 */
export function SyncPaymentDetails({ payment, platform, gateway, isAccurate }: SyncPaymentDetailsProps) {   
    if(isAccurate) {
      return (
        <div className={styles.accurateDetailWrapper}>
          <div className={styles.detailItem}>
            <p className={styles.detailLabel}>{__('Platform', 'give')}</p>
            <p className={styles.detailValue}>{platform ? capitalizeFirstLetter(platform) : __('Completed', 'give')}</p>
          </div>
          <CheckCircleIcon />
          <div className={styles.detailItem}>
            <p className={styles.detailLabel}>{__('Gateway', 'give')}</p>
            <p className={styles.detailValue}>{gateway ? capitalizeFirstLetter(gateway) : __('Completed', 'give')}</p>
          </div>
        </div>
      )
    }

    const PaymentAmount = () => (
        <strong className={styles.paymentDescriptionAmount}>{payment?.amount}</strong>
    );

    const description = createInterpolateElement(
        __('A donation of <PaymentAmount/> has been added.', 'give'),
        { PaymentAmount: <PaymentAmount /> }
    );

    return (
      <div className={styles.paymentDetails}>
        <div className={classnames(styles.paymentItem)}>
            <div className={styles.paymentIcon}>
                <HandHeartIcon />
            </div>
            <div className={styles.paymentsContent}>
                <p className={styles.paymentDescription}>{description}</p>
                <p className={styles.paymentDate}>{payment?.createdAt}</p>
            </div>
        </div>
      </div>
    );
}