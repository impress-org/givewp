import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import { formatTimestamp } from '@givewp/src/Admin/utils';
import type {Donation} from '@givewp/donations/admin/components/types';
import { useDonorEntityRecord } from '@givewp/donors/utils';
import { useCampaignEntityRecord } from '@givewp/campaigns/utils';
import Spinner from '@givewp/src/Admin/components/Spinner';
import styles from './styles.module.scss';



/**
 * @unreleased
 */
type GridProps = {
    children: React.ReactNode;
    ariaLabel: string;
};

/**
 * @unreleased
 */
export default function Grid({children, ariaLabel}: GridProps) {
    return (
        <div className={styles.container} role="group" aria-label={ariaLabel}>
            {children}
        </div>
    );
}

/**
 * @unreleased
 */
type GridCardProps = {
    children: React.ReactNode;
    heading: string;
    headingId: string;
    className?: string;
};

/**
 * @unreleased
 */
export function GridCard({children, heading, headingId, className}: GridCardProps) {
    return (
        <div className={classnames(styles.card, className)} role="region" aria-labelledby={`${headingId}-label`}>
            <h3 id={headingId}>{heading}</h3>
            {children}
        </div>
    );
}