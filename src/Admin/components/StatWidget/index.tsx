import {Spinner} from '@givewp/components';
import {HeaderText} from '../Header';
import PercentChangePill
    from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/CampaignStats/PercentChangePill';
import classnames from 'classnames';
import styles from './styles.module.scss';
import { __ } from '@wordpress/i18n';

/**
 * @since 4.6.0 add href & inActive props to handle Fee Recovery widget.
 * @since 4.4.0
 */
export type StatWidgetProps = {
    label: string;
    value: number;
    description?: string;
    formatter: Intl.NumberFormat;
    loading?: boolean;
    previousValue?: number;
    inActive?: boolean;
    href?: string;
};

/**
 * @since 4.6.0 use new props to handle Fee Recovery widget.
 * @since 4.4.0
 */
export default function StatWidget({
    label,
    value,
    description,
    href,
    formatter = null,
    loading = false,
    previousValue = null,
    inActive = false,
}: StatWidgetProps) {
    return (
        <div className={classnames(styles.statWidget)}>
            <header>
                <HeaderText>{label}</HeaderText>
            </header>
            <div className={styles.statWidgetAmount}>
                <div className={classnames(styles.statWidgetDisplay, {[styles.inActive]: inActive})}>
                    {!loading ? (
                        formatter?.format(value) ?? value
                    ) : (
                        <span>
                            <Spinner size="small" />
                        </span>
                    )}
                {inActive && (<a className={styles.upgradeLink} href={href} data-feerecovery-tooltip={__('Keep 100% of your fundraising revenue by providing donors with the option to cover the credit card processing fees', 'give')}>{__('Upgrade', 'give')}</a>)}
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
