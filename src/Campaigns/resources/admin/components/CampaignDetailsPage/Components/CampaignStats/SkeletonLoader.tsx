import styles from './styles.module.scss';

const SkeletonLoader = () => {
    return (
        <>
            <div className={styles.dateRangeFilter}>
                {[1, 2, 3, 4, 5].map((i) => (
                    <div key={i} className={styles.skeletonButton} />
                ))}
            </div>
            <div className={styles.mainGrid}>
                {/* Stat Widgets */}
                {[1, 2, 3].map((i) => (
                    <div key={i} className={styles.statWidget}>
                        <div className={styles.skeletonHeader} />
                        <div className={styles.skeletonValue} />
                        <div className={styles.skeletonFooter} />
                    </div>
                ))}
                
                {/* Revenue Widget */}
                <div className={styles.revenueWidget}>
                    <div className={styles.headerSpacing}>
                        <div className={styles.skeletonHeader} />
                        <div className={styles.skeletonSubHeader} />
                    </div>
                    <div className={styles.skeletonChart} />
                </div>

                {/* Nested Grid */}
                <div className={styles.nestedGrid}>
                    <div className={styles.progressWidget}>
                        <div className={styles.headerSpacing}>
                            <div className={styles.skeletonHeader} />
                            <div className={styles.skeletonSubHeader} />
                        </div>
                        <div className={styles.skeletonChart} />
                    </div>
                    <div className={styles.statWidget}>
                        <div className={styles.skeletonHeader} />
                        <div className={styles.skeletonValue} />
                    </div>
                </div>
            </div>
        </>
    );
};

export default SkeletonLoader; 