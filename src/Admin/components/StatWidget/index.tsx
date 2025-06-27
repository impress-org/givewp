import {Spinner} from '@givewp/components';
import {HeaderText} from '../Header';
import PercentChangePill
    from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/CampaignStats/PercentChangePill';
import styles from './styles.module.scss';

/**
 * @since 4.4.0
 */
export type StatWidgetProps = {
    label: string;
    value: number;
    description?: string;
    formatter: Intl.NumberFormat;
    loading?: boolean;
    previousValue?: number;
};

/**
 * @since 4.4.0
 */
export default function StatWidget({
    label,
    value,
    description,
    formatter = null,
    loading = false,
    previousValue = null,
}: StatWidgetProps) {
    return (
        <div className={styles.statWidget}>
            <header>
                <HeaderText>{label}</HeaderText>
            </header>
            <div className={styles.statWidgetAmount}>
                <div className={styles.statWidgetDisplay}>
                    {!loading ? (
                        formatter?.format(value) ?? value
                    ) : (
                        <span>
                            <Spinner size="small" />
                        </span>
                    )}
                </div>
                {previousValue !== null && <PercentChangePill value={value} comparison={previousValue} />}
            </div>
            {description && (
                <footer>
                    <div>{description}</div>
                </footer>
            )}
        </div>
    );
}
