import styles from './styles.module.scss';
import PercentChangePill from './PercentChangePill';
import HeaderText from '../HeaderText';
import type {StatWidgetProps} from './types';
import {Spinner} from '@givewp/components';

/**
 * @since 4.0.0
 */
const StatWidget = ({label, value, previousValue, description, formatter = null, loading = false}: StatWidgetProps) => {
    return (
        <div className={styles.statWidget}>
            <header>
                <HeaderText>{label}</HeaderText>
            </header>
            <div className={styles.statWidgetAmount}>
                <div className={styles.statWidgetDisplay}>
                    {!loading ? formatter?.format(value) ?? value : <span>{<Spinner />}</span>}
                </div>
                {previousValue !== null && <PercentChangePill value={value} comparison={previousValue} />}
            </div>
            <footer>
                <div>{description}</div>
            </footer>
        </div>
    );
};

export default StatWidget;
