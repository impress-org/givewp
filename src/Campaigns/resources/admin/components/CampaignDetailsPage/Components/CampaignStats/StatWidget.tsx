import styles from './styles.module.scss';
import PercentChangePill from './PercentChangePill';
import HeaderText from '../HeaderText';
import type {StatWidgetProps} from './types';

/**
 * Displays a statistic with optional loading state and previous value comparison
 * 
 * @since 4.0.0
 */
const StatWidget = ({
    label,
    value,
    previousValue,
    description,
    formatter = null,
    loading = false
}: StatWidgetProps) => {
    const renderValue = () => {
        if (loading) {
            return <div className={styles.skeletonNumber} />;
        }

        return formatter ? formatter.format(value) : value;
    };

    return (
        <div className={styles.statWidget}>
            <header>
                <HeaderText>{label}</HeaderText>
            </header>
            <div className={styles.statWidgetAmount}>
                <div className={styles.statWidgetDisplay}>
                    {renderValue()}
                </div>
                {previousValue !== null && (
                    <PercentChangePill value={value} comparison={previousValue} />
                )}
            </div>
            {description && (
                <footer>
                    <div>{description}</div>
                </footer>
            )}
        </div>
    );
};

export default StatWidget;
