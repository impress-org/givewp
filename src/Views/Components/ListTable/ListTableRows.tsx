import styles from './ListTableRows.module.scss';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {useEffect, useState} from 'react';
import TableCell, {IdBadge, StatusBadge} from "./TableCell";

const postStatusMap = {
    publish: __('published', 'give'),
    future: __('future', 'give'),
    draft: __('draft', 'give'),
    pending: __('pending', 'give'),
    trash: __('trash', 'give'),
    inherit: __('inherit', 'give'),
}

const RenderRow = ({ column, item }) => {
    const value = item?.[column.name];
    if(value === undefined){
        return null;
    }
    switch(column?.preset){
        case 'idBadge':
            return (
                <IdBadge key={column.name} id={value}/>
            );
        case 'statusBadge':
            return (
                <StatusBadge key={column.name} className={styles[value]}
                             text={value}
                />
            );
        case 'postStatus':
            return (
                <StatusBadge key={column.name} className={styles[value]}
                             text={postStatusMap[value]}
                />
            );
        default:
            return column?.render instanceof Function ? column.render(item) : value;
    }
}

export default function ListTableRows({columns, data, isValidating, rowActions, setUpdateErrors}) {
    const [removed, setRemoved] = useState([]);
    const [added, setAdded] = useState([]);

    useEffect(() => {
        if (added.length) {
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
    }, [added]);

    function removeRow(removeCallback) {
        return async (event) => {
            const id = event.target.dataset.actionid;
            setRemoved([id]);
            await removeCallback(id);
            setRemoved([]);
        }
    }

    function addRow(addCallback) {
        return async (event) => {
            const id = event.target.dataset.actionid;
            await addCallback(id);
            setAdded([id]);
        }
    }

    if(!data?.items) {
        return null;
    }

    return data.items.map((item) => (
        <tr
            key={item.id}
            className={cx(styles.tableRow, {
                [styles.deleted]: removed.indexOf(item.id) > -1,
                [styles.duplicated]: added.indexOf(parseInt(item.id)) > -1,
            })}
        >
            {columns.map((column) => (
                <TableCell key={column.name} className={column?.addClass} heading={column?.heading}>
                    <RenderRow column={column} item={item}/>
                    {!isValidating && rowActions &&
                        <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                            {column?.heading && rowActions({data, item, removeRow, addRow, setUpdateErrors})}
                        </div>
                    }
                </TableCell>
            ))}
        </tr>
    ));
}
