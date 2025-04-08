import React from 'react';
import Skeleton from 'react-loading-skeleton';
import 'react-loading-skeleton/dist/skeleton.css';
import styles from './index.module.scss';

const MultiStep = () => {
    return (
        <div className={styles.skeletonContainer}>
            {/* Header */}
            <div className={styles.skeletonHeader}>
                <Skeleton height={24} width="60%" className={styles.skeletonTitle} />
                <Skeleton height={8} width="100%" className={styles.skeletonProgressBar} />
            </div>

            {/* Section Label */}
            <Skeleton height={20} width="50%" className={styles.skeletonSectionLabel} />

            {/* Amount Buttons */}
            <div className={styles.skeletonGrid}>
                {Array.from({length: 6}).map((_, i) => (
                    <Skeleton key={i} height={50} borderRadius={8} />
                ))}
            </div>

            {/* Custom Amount Input */}
            <Skeleton height={50} width="100%" borderRadius={8} className={styles.skeletonInput} />

            {/* Donate Button */}
            <Skeleton height={50} width="100%" borderRadius={8} className={styles.skeletonButton} />

            {/* Secure Badge */}
            <div className={styles.skeletonBadge}>
                <Skeleton height={20} width={180} borderRadius={10} />
            </div>
        </div>
    );
};

export default MultiStep;
