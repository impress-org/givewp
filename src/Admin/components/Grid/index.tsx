import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
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
            <h3 id={headingId} className={styles.heading}>{heading}</h3>
            {children}
        </div>
    );
}