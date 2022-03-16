import styles from './DonationFormsTableRows.module.scss';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {useDonationForms} from '../api';
import {useEffect, useState} from 'react';
import RowAction from './RowAction';
import TableCell, {IdBadge, StatusBadge} from "./TableCell";

const statusMap = {
    publish: __('published', 'give'),
    future: __('future', 'give'),
    draft: __('draft', 'give'),
    pending: __('pending', 'give'),
    trash: __('trash', 'give'),
    inherit: __('inherit', 'give'),
}

const FormsRowActions = ({data, item, parameters, removeRow, addRow}) => {
    const trashEnabled = Boolean(data?.trash);

    if(parameters.status == 'trash') {
        return (
            <>
                <RowAction
                    onClick={removeRow('/restore', 'POST')}
                    actionId={item.id}
                    displayText={__('Restore', 'give')}
                    hiddenText={item.name}
                />
                <RowAction
                    onClick={removeRow('/delete', 'DELETE')}
                    actionId={item.id}
                    displayText={__('Delete Permanently', 'give')}
                    hiddenText={item.name}
                    highlight
                />
            </>
        );
    }

    return (
        <>
            <RowAction
                href={item.edit}
                displayText={__('Edit', 'give')}
                hiddenText={item.name}
            />
            <RowAction
                onClick={removeRow((trashEnabled ? '/trash' : '/delete'), 'DELETE')}
                actionId={item.id}
                highlight={!trashEnabled}
                displayText={trashEnabled ? __('Trash', 'give') : __('Delete', 'give')}
                hiddenText={item.name}
            />
            <RowAction
                href={item.permalink}
                displayText={__('View', 'give')}
                hiddenText={item.name}
            />
            <RowAction
                onClick={addRow('/duplicate', 'POST')}
                actionId={item.id}
                displayText={__('Duplicate', 'give')}
                hiddenText={item.name}
            />
        </>
    );
}

const RenderRow = ({ col, item }) => {
    switch(col?.preset){
        case 'idBadge':
            return (
                <IdBadge key={col.name} id={item[col.name]}/>
            );
        case 'statusBadge':
            return (
                <StatusBadge key={col.name} status={item[col.render]}
                             text={statusMap[item[col.render]]}
                />
            );
        default:
            return col.render instanceof Function ? col.render(item) : item[col.name];
    }
}

export default function DonationFormsTableRows({listParams, mutateForm, columns}) {
    const {data, isValidating} = useDonationForms(listParams);
    const [deleted, setDeleted] = useState([]);
    const [duplicated, setDuplicated] = useState([]);

    useEffect(() => {
        if (duplicated.length) {
            const timeouts = [];
            timeouts[0] = setTimeout(() => {
                const duplicateForm = document.getElementsByClassName(styles.duplicated);
                if (duplicateForm.length == 1) {
                    duplicateForm[0].scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            }, 100);
            timeouts[1] = setTimeout(() => {
                setDuplicated([]);
            }, 600);
            return () => {
                timeouts.forEach((timeout) => clearTimeout(timeout));
            };
        }
    }, [duplicated]);

    function removeRow(endpoint, method) {
        return async (event) => {
            const id = event.target.dataset.actionid;
            setDeleted([id]);
            await mutateForm(id, endpoint, method, true);
            setDeleted([]);
        }
    }

    function addRow(endpoint, method) {
        return async (event) => {
            const id = event.target.dataset.actionid;
            const response = await mutateForm(id, endpoint, method);
            setDuplicated([...response.successes]);
        }
    }

    if(!data?.forms) {
        return null;
    }

    return data.forms.map((form) => (
        <tr
            key={form.id}
            className={cx(styles.tableRow, {
                [styles.deleted]: deleted.indexOf(form.id) > -1,
                [styles.duplicated]: duplicated.indexOf(parseInt(form.id)) > -1,
            })}
        >
            {columns.map((column) => (
                <TableCell key={column.name} className={column?.addClass}
                           heading={column?.header}
                >
                    <RenderRow col={column} item={form}/>
                </TableCell>
            ))}
            <th className={cx(styles.tableCell, styles.tableRowHeader)} scope="row">
                <a href={form.edit}>{form.name}</a>
                <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                    {!isValidating &&
                    <FormsRowActions
                        data={data}
                        item={form}
                        parameters={listParams}
                        removeRow={removeRow}
                        addRow={addRow}
                    />}
                </div>
            </th>
        </tr>
    ));
}
