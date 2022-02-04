import loadingForms from "../loadingForms.json";
import styles from "./DonationFormsTableRows.module.scss";
import {__} from "@wordpress/i18n";
import cx from "classnames";
import { useDonationForms } from "../api";

export default function DonationFormsTableRows({listParams, mutateForm, status}) {
    const {data, error, isValidating} = useDonationForms(listParams);

    async function deleteForm(event) {
        const endpoint = data.trash ? '/trash' : '/delete';
        await mutateForm(event.target.dataset.formid, endpoint, 'DELETE');
    }

    async function duplicateForm(event) {
        await mutateForm(event.target.dataset.formid, '/duplicate', 'POST');
    }

    async function restoreForm(event) {
        await mutateForm(event.target.dataset.formid, '/restore', 'POST');
    }

    const forms = data ? data.forms : loadingForms;
    const trash = data ? data.trash : false;

    //general error state
    if (error && !isValidating) {
        return (
            <>
                <tr className={styles.tableRow}>
                    <td colSpan={9} className={styles.statusMessage}>
                        {__('There was a problem retrieving the donation forms.', 'give')}
                    </td>
                </tr>
                <tr className={styles.tableRow}>
                    <td colSpan={9} className={styles.statusMessage}>
                        {__('Click', 'give') + ' '}
                        <a href={'edit.php?post_type=give_forms&page=give-forms'}>{__('here', 'give')}</a>
                        {' ' + __('to reload the page.')}
                    </td>
                </tr>
            </>
        );
    }

    return forms.map((form) => (
        <tr key={form.id} className={cx(
            styles.tableRow,
            {
                [styles.loading]: !data,
            }
        )}>
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
                            >
                                {__('Restore', 'give')} <span className="give-visually-hidden">{form.name}</span>
                            </button>
                            <button
                                type="button"
                                onClick={deleteForm}
                                data-formid={form.id}
                                className={cx(styles.action, styles.delete)}
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
                            >
                                {__('Duplicate', 'give')}
                            </button>
                        </>
                    )}
                </div>
            </th>
            <td className={styles.tableCell} style={{textAlign: 'end'}}>
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
                        {form.goal.actual} {__('of', 'give')}{' '}
                        {form.goal.goal ? (
                            <a href={`${form.edit}&give_tab=donation_goal_options`}>
                                {form.goal.goal}
                                {form.goal.format != 'amount' ? ` ${form.goal.format}` : null}
                            </a>
                        ) : null}
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
                <a href={`edit.php?post_type=give_forms&page=give-reports&tab=forms&form-id=${form.id}`}>
                    {form.revenue}
                </a>
            </td>
            <td className={styles.tableCell}>
                <input type="text" aria-label={__('Copy shortcode', 'give')} readOnly value={form.shortcode} />
            </td>
            <td className={styles.tableCell}>{form.datetime}</td>
            <td className={styles.tableCell}>
                <div className={cx(styles.statusBadge, styles[form.status])}>{form.status}</div>
            </td>
        </tr>
    ));
}
