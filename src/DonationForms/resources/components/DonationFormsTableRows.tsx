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
                <StatusBadge key={col.name} status={item[col.name]}
                             text={statusMap[item[col.name]]}
                />
            );
        default:
            return col.render instanceof Function ? col.render(item) : item[col.name];
    }
}

export default function DonationFormsTableRows({listParams, mutateForm, columns}) {
    const {data, isValidating} = useDonationForms(listParams);
    const [removed, setRemoved] = useState([]);
    const [added, setAdded] = useState([]);

    useEffect(() => {
        if (added.length) {
            const timeouts = [];
            timeouts[0] = setTimeout(() => {
                const duplicateForm = document.getElementsByClassName(styles.duplicated);
                if (duplicateForm.length == 1) {
                    duplicateForm[0].scrollIntoView({behavior: 'smooth', block: 'center'});
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

    function removeRow(endpoint, method) {
        return async (event) => {
            const id = event.target.dataset.actionid;
            setRemoved([id]);
            await mutateForm(id, endpoint, method, true);
            setRemoved([]);
        }
    }

    function addRow(endpoint, method) {
        return async (event) => {
            const id = event.target.dataset.actionid;
            const response = await mutateForm(id, endpoint, method);
            setAdded([...response.successes]);
        }
    }

    if(!data?.forms) {
        return null;
    }

    return data.forms.map((item) => (
        <tr
            key={item.id}
            className={cx(styles.tableRow, {
                [styles.deleted]: removed.indexOf(item.id) > -1,
                [styles.duplicated]: added.indexOf(parseInt(item.id)) > -1,
            })}
        >
            {columns.map((column) => (
                <TableCell key={column.name} className={column?.addClass}
                           heading={column?.heading}
                >
                    <RenderRow col={column} item={item}/>
                    {!isValidating && column?.heading &&
                        <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                            <FormsRowActions
                                data={data}
                                item={item}
                                parameters={listParams}
                                removeRow={removeRow}
                                addRow={addRow}
                            />
                        </div>
                    }
                </TableCell>
            ))}
        </tr>
    ));
}
