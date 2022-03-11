import styles from './DonationFormsTableRows.module.scss';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {useDonationForms} from '../api';
import {useEffect, useState} from 'react';

const statusMap = {
    publish: __('published', 'give'),
    future: __('future', 'give'),
    draft: __('draft', 'give'),
    pending: __('pending', 'give'),
    trash: __('trash', 'give'),
    inherit: __('inherit', 'give'),
}

export default function DonationFormsTableRows({listParams, mutateForm, status}) {
    const {data, isValidating} = useDonationForms(listParams);
    const [deleted, setDeleted] = useState([]);
    const [duplicated, setDuplicated] = useState([]);
    const [busy, setBusy] = useState(false);
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

    async function deleteForm(event) {
        const endpoint = data.trash ? '/trash' : '/delete';
        setBusy(true);
        setDeleted([event.target.dataset.formid]);
        await mutateForm(event.target.dataset.formid, endpoint, 'DELETE');
        setBusy(false);
        setDeleted([]);
    }

    async function duplicateForm(event) {
        const id = event.target.dataset.formid;
        setBusy(true);
        const response = await mutateForm(id, '/duplicate', 'POST');
        setDuplicated([...response.successes]);
        setBusy(false);
    }

    async function restoreForm(event) {
        setBusy(true);
        setDeleted([event.target.dataset.formid]);
        await mutateForm(event.target.dataset.formid, '/restore', 'POST');
        setDeleted([]);
    }

    if(!data?.forms) {
        return false;
    }

    const trash = data ? data.trash : false;

    return data.forms.map((form) => (
        <tr
            key={form.id}
            className={cx(styles.tableRow, {
                [styles.deleted]: deleted.indexOf(form.id) > -1,
                [styles.duplicated]: duplicated.indexOf(parseInt(form.id)) > -1,
                [styles.unclickable]: isValidating,
            })}
        >
            <td className={styles.tableCell}>
                <div className={styles.idBadge}>{form.id}</div>
            </td>
            <th className={cx(styles.tableCell, styles.tableRowHeader)} scope="row">
                <a href={form.edit}>{form.name}</a>
                <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                    {status == 'trash' ? (
                        <>
                            <button
                                type="button"
                                onClick={restoreForm}
                                data-formid={form.id}
                                className={styles.action}
                                disabled={busy}
                            >
                                {__('Restore', 'give')} <span className="give-visually-hidden">{form.name}</span>
                            </button>
                            <button
                                type="button"
                                onClick={deleteForm}
                                data-formid={form.id}
                                className={cx(styles.action, styles.delete)}
                                disabled={busy}
                            >
                                {__('Delete Permanently', 'give')}{' '}
                                <span className="give-visually-hidden">{form.name}</span>
                            </button>{' '}
                        </>
                    ) : (
                        <>
                            <a href={form.edit} className={styles.action}>
                                {__('Edit', 'give')} <span className="give-visually-hidden">{form.name}</span>
                            </a>
                            <button
                                type="button"
                                onClick={deleteForm}
                                data-formid={form.id}
                                className={cx(styles.action, {[styles.delete]: !trash})}
                                disabled={busy}
                            >
                                {trash ? __('Trash', 'give') : __('Delete', 'give')}{' '}
                                <span className="give-visually-hidden">{form.name}</span>
                            </button>
                            <a href={form.permalink}>{__('View', 'give')}</a>
                            <button
                                type="button"
                                onClick={duplicateForm}
                                data-formid={form.id}
                                className={styles.action}
                                disabled={busy}
                            >
                                {__('Duplicate', 'give')}
                            </button>
                        </>
                    )}
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
                                    width: Math.max(Math.min(form.goal.progress, 100), 0) + '%',
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
