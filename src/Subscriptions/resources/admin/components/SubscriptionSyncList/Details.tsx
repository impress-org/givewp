import { __ } from "@wordpress/i18n";
import { createInterpolateElement } from '@wordpress/element';
import classnames from 'classnames';
import { CheckCircleIcon, HandHeartIcon, XCircleIcon } from "./icons";
import styles from "./styles.module.scss";

/**
 * @unreleased
 */
type SyncDetailsProps = {
    isUpdated: boolean;
    currentValue: string;
  }
  
/**
 * @unreleased
 */
export default function SyncDetails({ isUpdated, currentValue }: SyncDetailsProps) {
  return (
    <div className={styles.syncDetails}>
      {isUpdated ? (
        <div className={styles.detail}>
          <div className={styles.detailContainer}>
            <p className={styles.detailTitle}>{__('OLD', 'give')}</p>
            <div className={styles.detailWrapper}>
              <div className={styles.detailItem}>
                  <p className={styles.detailLabel}>{__('Platform', 'give')}</p>
                  <p className={styles.detailStatus}>{currentValue}</p>
                </div>
                <XCircleIcon />
                <div className={styles.detailItem}>
                  <p className={styles.detailLabel}>{__('Gateway', 'give')}</p>
                  <p className={styles.detailStatus}>{__('Completed', 'give')}</p>
              </div>
            </div>

          </div>

          <div className={styles.detailContainer}>
            <p className={styles.detailTitle}>{__('NEW', 'give')}</p>
            <div className={styles.detailWrapper}>
                <div className={styles.detailItem}>
                  <p className={styles.detailLabel}>{__('Platform', 'give')}</p>
                  <p className={styles.detailStatus}>{__('Completed', 'give')}</p>
                </div>
                <CheckCircleIcon />
                <div className={styles.detailItem}>
                  <p className={styles.detailLabel}>{__('Gateway', 'give')}</p>
                  <p className={styles.detailStatus}>{__('Completed', 'give')}</p>
                </div>
              </div>
            </div>
        </div>
      ) : (
        <div className={styles.accurateDetailWrapper}>
          <div className={styles.detailItem}>
            <p className={styles.detailLabel}>{__('Platform', 'give')}</p>
            <p className={styles.detailStatus}>{__('Completed', 'give')}</p>
          </div>
          <CheckCircleIcon />
          <div className={styles.detailItem}>
            <p className={styles.detailLabel}>{__('Gateway', 'give')}</p>
            <p className={styles.detailStatus}>{__('Completed', 'give')}</p>
          </div>
        </div>
      )}
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
  };
}

/**
 * @unreleased
 */
export function SyncPaymentDetails({ payment }: SyncPaymentDetailsProps) {    
    const PaymentAmount = () => (
        <strong className={styles.paymentDescriptionAmount}>{payment?.amount}</strong>
    );

    const description = createInterpolateElement(
        __('A donation of <PaymentAmount/> has been added.', 'give'),
        { PaymentAmount: <PaymentAmount /> }
    );

    return (
      <div className={styles.paymentDetails}>
        <div key={payment?.id} className={classnames(styles.paymentItem)}>
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