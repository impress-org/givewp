import React from 'react';
import Skeleton from 'react-loading-skeleton';
import 'react-loading-skeleton/dist/skeleton.css';
import styles from './index.module.scss';

const Classic = ({showHeader = true}) => {
  return (
      <div className={styles.skeletonContainer}>
          {/* Header */}
          {showHeader && (
              <div className={styles.skeletonHeader}>
                  <Skeleton width={250} height={30} />
                  <Skeleton width={350} height={20} style={{marginTop: 10}} />
                  <div className="skeleton-badge">
                      <Skeleton width={180} height={30} borderRadius={20} />
                  </div>
              </div>
          )}

          <div className={styles.skeletonSection}>
              {/* Donation Amount */}
              <Skeleton width={250} height={25} />
              <Skeleton width={300} height={20} style={{marginTop: 8}} />

              <div className={styles.skeletonGrid}>
                  {[...Array(6)].map((_, i) => (
                      <Skeleton key={i} height={50} borderRadius={6} />
                  ))}
              </div>

              <Skeleton height={50} borderRadius={6} style={{marginTop: 10}} />

              {/* Donor Info */}
              <div className={styles.skeletonSubsection}>
                  <Skeleton width={200} height={25} />
                  <Skeleton width={300} height={15} style={{marginTop: 8}} />

                  <div className={styles.skeletonRow}>
                      <Skeleton height={45} borderRadius={6} />
                      <Skeleton height={45} borderRadius={6} />
                  </div>

                  <Skeleton height={45} borderRadius={6} style={{marginTop: 10}} />
              </div>

              {/* Payment Details */}
              <div className={styles.skeletonSubsection}>
                  <Skeleton width={200} height={25} />
                  <Skeleton width={300} height={15} style={{marginTop: 8}} />

                  <div className={styles.skeletonSummary}>
                      <Skeleton height={100} />
                  </div>

                  <Skeleton width={100} height={20} style={{marginTop: 12}} />

                  <div className={styles.skeletonPaymentOptions}>
                      <Skeleton height={40} borderRadius={6} />
                      <Skeleton height={100} borderRadius={6} />
                      <Skeleton height={40} borderRadius={6} />
                      <Skeleton height={40} borderRadius={6} />
                  </div>
              </div>

              {/* Final Button */}
              <div className={styles.skeletonButton}>
                  <Skeleton height={45} borderRadius={6} />
              </div>
          </div>
      </div>
  );
};

export default Classic;
