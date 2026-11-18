import styles from './TableCell.module.scss';
import cx from 'classnames';

/**
 * @since TBD The table cell component is gone with the old table; the badge stays for confirm lists.
 */
export function IdBadge({id, addClass = ''}) {
    return <div className={cx(styles.idBadge, addClass)}>{id}</div>;
}
