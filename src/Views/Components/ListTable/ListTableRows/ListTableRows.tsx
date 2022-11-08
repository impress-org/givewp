import styles from './ListTableRows.module.scss';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {useEffect, useState} from 'react';
import TableCell from '../TableCell/TableCell';
import {BulkActionCheckbox} from '@givewp/components/ListTable/BulkActions/BulkActionCheckbox';
import InterweaveSSR from '@givewp/components/ListTable/InterweaveSSR';

//@unreleased determines if row should display actions based on column.id
const displayRowActions = (column) => {
    switch (column.id) {
        case 'id':
            return true;
        case 'donorInformation':
            return true;
        default:
            return false;
    }
};

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
                <BulkActionCheckbox id={item.id} name={item?.donor} singleName={singleName} />
            </TableCell>
            <>
                {columns.map((column) => {
                    return (
                        <TableCell
                            key={column.id}
                            className={cx(column?.addClass, {
                                [styles[align]]: !column?.alignColumn,
                                [styles.center]: column?.alignColumn === 'center',
                                [styles.start]: column?.alignColumn === 'start',
                                [styles.start]: column?.alignColumn === 'start',
                            })}
                            heading={displayRowActions(column)}
                        >
                            <InterweaveSSR column={column} item={item} />
                            {!isLoading && rowActions && (
                                <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                                    {displayRowActions(column) &&
                                        rowActions({
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
