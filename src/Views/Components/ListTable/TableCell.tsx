import styles from './TableCell.module.scss';
import cx from 'classnames';

export default function TableCell({className = '', children = null, heading = false}) {
    if (heading) {
        return (
            <th className={cx(styles.tableCell, styles.tableRowHeader, className)} scope="row">
                {children}
            </th>
        );
    }

    return <td className={cx(styles.tableCell, className)}>{children}</td>;
}

export function IdBadge({id, addClass = ''}) {
    return <div className={cx(styles.idBadge, addClass)}>{id}</div>;
}
