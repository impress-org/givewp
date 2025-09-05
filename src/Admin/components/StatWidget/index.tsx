import {Spinner} from '@givewp/components';
import {HeaderText} from '../Header';
import PercentChangePill
    from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/CampaignStats/PercentChangePill';
import classnames from 'classnames';
import styles from './styles.module.scss';
import { __ } from '@wordpress/i18n';

/**
 * @unreleased add className & toolTipDescription prop for dynamic tooltips.
 * @since 4.6.0 add href & inActive props to handle Fee Recovery widget.
 * @since 4.4.0
 */
export type StatWidgetProps = {
    label: string;
    value: string | React.ReactNode;
    description?: string;
    loading?: boolean;
    inActive?: boolean;
    href?: string;
    className?: string;
    toolTipDescription?: string;
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
    loading = false,
    inActive = false,
    className,
    toolTipDescription,
}: StatWidgetProps) {
    return (
        <div className={classnames(styles.statWidget, className)}>
            <header>
                <HeaderText>{label}</HeaderText>
            </header>
            <div className={styles.statWidgetAmount}>
                <div className={classnames(styles.statWidgetDisplay, {[styles.inActive]: inActive})}>
                    {!loading ? (
                        value
                    ) : (
                        <span>
                            <Spinner size="small" />
                        </span>
                    )}
                {inActive && (<a className={styles.upgradeLink} href={href} data-addon-tooltip={toolTipDescription}>{__('Upgrade', 'give')}</a>)}
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
