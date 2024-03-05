import cx from 'classnames';
import styles from '../SectionTable/SectionTable.module.scss';

export default function SectionTable({tableHeaders, data, blankSlate = null}) {
    const isEmpty = data.length === 0;
    const tableKeys = Object.keys(tableHeaders);

    return (
        <div className={styles.tableGroup}>
            <table className={styles.table}>
                <thead>
                    <tr>
                        {tableKeys.map((key) => (
                            <th className={cx(styles.tableColumnHeader, {[styles.idColumn]: key === 'id'})} key={key}>
                                {tableHeaders[key]}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {data.map((row, index) => (
                        <tr className={styles.tableRow} key={index}>
                            {tableKeys.map((key) => (
                                <td className={`${styles.tableCell} ${styles[key] ?? ''}`} key={key}>
                                    {key === 'id' ? <span className={styles.idBadge}>{row[key]}</span> : row[key]}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
            <div id="giveListTableMessage">
                {isEmpty && (
                    <div role="status" className={styles.statusMessage}>
                        {blankSlate}
                    </div>
                )}
            </div>
        </div>
    );
}
