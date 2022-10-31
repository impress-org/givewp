import styles from './ListTableRows.module.scss';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {Markup} from 'interweave';
import {useEffect, useState} from 'react';
import TableCell, {IdBadge} from './TableCell';
import {BulkActionCheckbox} from '@givewp/components/ListTable/BulkActionCheckbox';

const postStatusMap = {
    publish: __('published', 'give'),
    future: __('future', 'give'),
    draft: __('draft', 'give'),
    pending: __('pending', 'give'),
    trash: __('trash', 'give'),
    inherit: __('inherit', 'give'),
    private: __('private', 'give'),
};

const donationStatusMap = {
    publish: __('complete', 'give'),
    pending: __('pending', 'give'),
    refunded: __('refunded', 'give'),
    failed: __('failed', 'give'),
    cancelled: __('cancelled', 'give'),
    abandoned: __('abandoned', 'give'),
    preapproval: __('pre-approved', 'give'),
    processing: __('processing', 'give'),
    revoked: __('revoked', 'give'),
    give_subscription: __('renewal', 'give'),
};

const RenderRow = ({column, item}) => {
    let value = item?.[column.name];
    if (value === undefined) {
        value = null;
    }

    function isHTML(column) {
        const displaysHtml = ['paymentType', 'name', 'formTitle', 'status', 'donorType'];
        return displaysHtml.some((name) => name === column.name);
    }

    if (column.name === 'id') {
        return <IdBadge key={column.name} id={value} />;
    } else if (isHTML(column)) {
        // return <Markup content={"<a href='/tag'>html</a>"} />;
        return <Markup content={"<img src='' alt=''/>"} />;
    } else {
        if (value === '' || value === null) return '-';
        return value;
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

    return data.map((item) => (
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
                        })}
                        heading={column?.heading}
                    >
                        <RenderRow column={column} item={item} />
                        {!isLoading && rowActions && (
                            <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                                {column?.heading &&
                                    rowActions({data, item, removeRow, addRow, setUpdateErrors, parameters})}
                            </div>
                        )}
                    </TableCell>
                ))}
            </>
        </tr>
    ));
}
