import styles from './DonationFormsTableRows.module.scss';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {useDonationForms} from '../api';
import {useEffect, useState} from 'react';
import RowAction from './RowAction';

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
                data-formid={item.id}
                displayText={__('Duplicate', 'give')}
                hiddenText={item.name}
            />
        </>
    );
}

export default function DonationFormsTableRows({listParams, mutateForm}) {
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
        return false;
    }

    return data.forms.map((form) => (
        <tr
            key={form.id}
            className={cx(styles.tableRow, {
                [styles.deleted]: deleted.indexOf(form.id) > -1,
                [styles.duplicated]: duplicated.indexOf(parseInt(form.id)) > -1,
            })}
        >
            <td className={styles.tableCell}>
                <div className={styles.idBadge}>{form.id}</div>
            </td>
            <th className={cx(styles.tableCell, styles.tableRowHeader)} scope="row">
                <a href={form.edit}>{form.name}</a>
                <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                    {!isValidating && <FormsRowActions
                        data={data}
                        item={form}
                        parameters={listParams}
                        removeRow={removeRow}
                        addRow={addRow}
                    />}
                </div>
            </th>
            <td className={cx(styles.tableCell, styles.monetary)}>
                {form.amount}
            </td>
            <td className={styles.tableCell}>
                {form.goal ? (
                    <>
                        <div className={styles.goalProgress}>
                            <span
                                style={{
                                    width: form.goal.format == 'percentage' ?
                                        form.goal.actual :
                                        Math.max(Math.min(form.goal.progress, 100), 0) + '%'
                                }}
                            />
                        </div>
                        <span className={cx({[styles.monetary]: form.goal.format == __('amount', 'give')})}>
                            {form.goal.actual}
                        </span>
                        {form.goal.format != 'percentage' && (
                            <>
                                {' '}
                                {__('of', 'give')}{' '}
                                <a href={`${form.edit}&give_tab=donation_goal_options`}>
                                    {form.goal.goal}
                                    {form.goal.format != __('amount', 'give') ? ` ${form.goal.format}` : null}
                                </a>
                            </>
                        )}
                        {form.goal.progress >= 100 && (
                            <p>
                                <span className={cx('dashicons dashicons-star-filled', styles.star)}></span>
                                {__('Goal achieved!', 'give')}
                            </p>
                        )}
                    </>
                ) : (
                    <span>{__('No Goal Set', 'give')}</span>
                )}
            </td>
            <td className={styles.tableCell}>
                <a href={`edit.php?post_type=give_forms&page=give-payment-history&form_id=${form.id}`}>
                    {form.donations}
                </a>
            </td>
            <td className={styles.tableCell}>
                <a href={`edit.php?post_type=give_forms&page=give-reports&tab=forms&legacy=true&form-id=${form.id}`}>
                    {form.revenue}
                </a>
            </td>
            <td className={styles.tableCell}>
                <input
                    type="text"
                    aria-label={__('Copy shortcode', 'give')}
                    readOnly
                    value={form.shortcode}
                    className={styles.shortcode}
                />
            </td>
            <td className={styles.tableCell}>{form.datetime}</td>
            <td className={styles.tableCell}>
                <div className={cx(styles.statusBadge, styles[form.status])}>
                    {statusMap[form.status] || __('none', 'give')}
                </div>
            </td>
        </tr>
    ));
}
