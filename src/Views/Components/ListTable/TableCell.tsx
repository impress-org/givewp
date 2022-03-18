import styles from './TableCell.module.scss';
import cx from 'classnames';
import {__} from "@wordpress/i18n";

export default function TableCell({className = '', children = null, heading = false}) {
    if(heading){
        return (
            <th className={cx(styles.tableCell, styles.tableRowHeader, className)} scope="row">
                {children}
            </th>
        );
    }

    return (
        <td className={cx(styles.tableCell, className)}>
            {children}
        </td>
    );
}

export function IdBadge({ id }){
    return (
        <div className={styles.idBadge}>{id}</div>
    );
}

export function StatusBadge({ className, text}){
    return (
        <div className={cx(styles.statusBadge, className)}>
            {text || __('none', 'give')}
        </div>
    );
}
