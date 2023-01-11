import styles from './ListTableRows.module.scss';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {useEffect, useState} from 'react';
import TableCell from '../TableCell';
import {BulkActionCheckbox} from '@givewp/components/ListTable/BulkActions/BulkActionCheckbox';
import InterweaveSSR from '@givewp/components/ListTable/InterweaveSSR';

export default function ListTableRows({columns, data, isLoading, rowActions, setUpdateErrors, parameters, singleName}) {
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
                <BulkActionCheckbox
                    id={item.id}
                    name={item?.donor ?? item?.title ?? item?.donorInformation}
                    singleName={singleName}
                    data={data}
                />
            </TableCell>
            <>
                {columns?.map((column) => {
                    return (
                        <TableCell key={column.id} heading={columns[0].id === column.id}>
                            <InterweaveSSR column={column} item={item} />
                            {columns[0].id === column.id && !isLoading && rowActions && (
                                <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                                    {rowActions({
                                        data,
                                        item,
                                        removeRow,
                                        addRow,
                                        setUpdateErrors,
                                        parameters,
                                    })}
                                </div>
                            )}
                        </TableCell>
                    );
                })}
            </>
        </tr>
    ));
}
