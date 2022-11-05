import styles from './ListTableRows.module.scss';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {useEffect, useState} from 'react';
import TableCell from './TableCell';
import {BulkActionCheckbox} from '@givewp/components/ListTable/BulkActionCheckbox';
import RenderRow from '@givewp/components/ListTable/RenderRow';

export default function ListTableRows({
    columns,
    data,
    isLoading,
    rowActions,
    setUpdateErrors,
    parameters,
    singleName,
    align,
}) {
    const [removed, setRemoved] = useState([]);
    const [added, setAdded] = useState([]);

    useEffect(() => {
        if (added.length && !isLoading) {
            const timeouts = [];
            timeouts[0] = setTimeout(() => {
                const addedItem = document.getElementsByClassName(styles.duplicated);
                if (addedItem.length == 1) {
                    addedItem[0].scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            }, 100);
            timeouts[1] = setTimeout(() => {
                setAdded([]);
            }, 600);
            return () => {
                timeouts.forEach((timeout) => clearTimeout(timeout));
            };
        }
    }, [added, isLoading]);

    function removeRow(removeCallback) {
        return async (event) => {
            const id = event.target.dataset.actionid;
            setRemoved([id]);
            await removeCallback(id);
            setRemoved([]);
        };
    }

    function addRow(addCallback) {
        return async (event) => {
            const id = event.target.dataset.actionid;
            const addedItem = await addCallback(id);
            setAdded([...addedItem.successes]);
        };
    }

    if (!data) {
        return null;
    }

    return data?.items.map((item) => (
        <tr
            key={item.id}
            className={cx(styles.tableRow, {
                [styles.deleted]: removed.indexOf(item.id) > -1,
                [styles.duplicated]: added.indexOf(parseInt(item.id)) > -1,
            })}
        >
            <TableCell>
                <BulkActionCheckbox id={item.id} name={item?.name} singleName={singleName} />
            </TableCell>
            <>
                {columns.map((column) => (
                    <TableCell
                        key={column.name}
                        className={cx(column?.addClass, {
                            [styles[align]]: !column?.alignColumn,
                            [styles.center]: column?.alignColumn === 'center',
                            [styles.start]: column?.alignColumn === 'start',
                            [styles.start]: column?.alignColumn === 'start',
                        })}
                        heading={column?.heading}
                    >
                        <RenderRow column={column} item={item} />
                        {!isLoading && rowActions && (
                            <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                                {column?.name === 'id' ||
                                    (column?.name === 'name' &&
                                        rowActions({data, item, removeRow, addRow, setUpdateErrors, parameters}))}
                            </div>
                        )}
                    </TableCell>
                ))}
            </>
        </tr>
    ));
}
