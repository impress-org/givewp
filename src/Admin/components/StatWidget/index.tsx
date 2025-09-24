import {Spinner} from '@givewp/components';
import {HeaderText} from '../Header';
import PercentChangePill
    from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/CampaignStats/PercentChangePill';
import classnames from 'classnames';
import styles from './styles.module.scss';
import { __ } from '@wordpress/i18n';

/**
 * @since 4.10.0 replace inActive with upgrade object.
 * @since 4.6.0 add href & inActive props to handle Fee Recovery widget.
 * @since 4.4.0
 */
export type StatWidgetProps = {
    label: string;
    value: string | React.ReactNode;
    description?: string;
    loading?: boolean;
    className?: string;
    upgrade?: {
        href: string;
        tooltip: string;
    };
};

/**
 * @since 4.10.0 use upgrade object instead of inActive.
 * @since 4.6.0 use new props to handle Fee Recovery widget.
 * @since 4.4.0
 */
export default function StatWidget({
    label,
    value,
    description,
    upgrade = null,
    loading = false,
    className,
}: StatWidgetProps) {
    return (
        <div className={classnames(styles.statWidget, className)}>
            <header>
                <HeaderText>{label}</HeaderText>
            </header>
            <div className={styles.statWidgetAmount}>
                <div className={classnames(styles.statWidgetDisplay, {[styles.requiresUpgrade]: upgrade})}>
                    {!loading ? (
                        value
                    ) : (
                        <span>
                            <Spinner size="small" />
                        </span>
                    )}
                {upgrade && (<a className={styles.upgradeLink} href={upgrade?.href} data-addon-tooltip={upgrade?.tooltip}>{__('Upgrade', 'give')}</a>)}
                </div>
            </div>
            {description && (
                <footer>
                    <div>{description}</div>
                </footer>
            )}

        </div>
    );
}
