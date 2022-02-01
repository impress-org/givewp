import {StrictMode, useEffect, useState} from 'react';
import ReactDOM from 'react-dom';
import {__, _n} from '@wordpress/i18n';
import cx from 'classnames';
import {mutate} from 'swr';

import styles from './admin-donation-forms.module.scss';
import Pagination from './components/Pagination.js';
import Shortcode from './components/Shortcode';
import loadingForms from './loadingForms.json';
import {fetchWithArgs, keyFunction, useDonationForms} from './api';

type DonationForm = {
    id: number;
    name: string;
    amount: string;
    goal: string | {progress: number; format: string; actual: string; goal: string};
    donations: number;
    revenue: string;
    datetime: string;
    shortcode: string;
    status: string;
    permalink: string;
    edit: string;
};

function AdminDonationForms() {
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [errors, setErrors] = useState(0);
    const [successes, setSuccesses] = useState(0);
    const [status, setStatus] = useState('any');
    const {data, error, isValidating} = useDonationForms({page, perPage, status});

    async function mutateForm(event, endpoint, method) {
        try {
            const response = await fetchWithArgs(endpoint, {page, perPage, ids: event.target.dataset.formid}, method);
            //mutate the data without revalidating current page
            const currentKey = keyFunction({page, perPage, status});
            await mutate(currentKey, {...data, ...response}, false);
            //revalidate all pages after the current page and null their data
            const mutations = [];
            for (let i = response.page + 1; i <= Math.ceil(data.total / perPage); i++) {
                const invalidKey = keyFunction({page: i, perPage, status});
                if (invalidKey != currentKey) {
                    mutations.push(mutate(invalidKey, null));
                }
            }
            setErrors(response.errors);
            setSuccesses(response.successes);
        } catch (error) {
            console.error(error.message);
        }
    }

    function deleteForm(event) {
        const endpoint = data.trash ? '/trash' : '/delete';
        mutateForm(event, endpoint, 'DELETE');
    }

    function duplicateForm(event) {
        mutateForm(event, '/duplicate', 'POST');
    }

    function changeStatus(event) {
        setStatus(event.target.value);
    }

    function TableRows({data}) {
        const forms = data ? data.forms : loadingForms;
        const trash = data ? data.trash : false;

        if (forms.length == 0) {
            return (
                <tr className={styles.tableRow}>
                    <td colSpan={9} className={styles.statusMessage}>
                        {__('No donation forms found.', 'give')}
                    </td>
                </tr>
            );
        }

        //general error state
        if (error) {
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
            <tr key={form.id} className={cx(styles.tableRow, !data && styles.loading)}>
                <td className={styles.tableCell}>
                    <div className={styles.idBadge}>{form.id}</div>
                </td>
                <th className={cx(styles.tableCell, styles.tableRowHeader)} scope="row">
                    <a href={form.edit}>{form.name}</a>
                    <div role="group" aria-label={__('Actions', 'give')} className={styles.tableRowActions}>
                        <a href={form.edit} className={styles.action}>
                            Edit <span className="give-visually-hidden">{form.name}</span>
                        </a>
                        <button type="button" onClick={deleteForm} data-formid={form.id} className={styles.action}>
                            {trash ? __('Trash', 'give') : __('Delete', 'give')}{' '}
                            <span className="give-visually-hidden">{form.name}</span>
                        </button>
                        <a href={form.permalink}>{__('View', 'give')}</a>
                        <button type="button" onClick={duplicateForm} data-formid={form.id} className={styles.action}>
                            {__('Duplicate', 'give')}
                        </button>
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
                            <a href={`${form.edit}&give_tab=donation_goal_options`}>{form.goal.actual}</a>
                            {form.goal.goal ? (
                                <span>
                                    {' '}
                                    {__('of', 'give')} {form.goal.goal}{' '}
                                    {form.goal.format != 'amount' ? form.goal.format : null}
                                </span>
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
                    <Shortcode code={form.shortcode} />
                </td>
                <td className={styles.tableCell}>{form.datetime}</td>
                <td className={styles.tableCell}>
                    <div className={cx(styles.statusBadge, styles[form.status])}>{form.status}</div>
                </td>
            </tr>
        ));
    }

    return (
        <article>
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{__('Donation Forms', 'give')}</h1>
                <a href="post-new.php?post_type=give_forms" className={styles.button}>
                    {__('Add Form', 'give')}
                </a>
            </div>
            <div className={styles.searchContainer}>
                <select onChange={changeStatus}>
                    <option value="any">{__('All','give')}</option>
                    <option value="publish">{__('Published','give')}</option>
                    <option value="pending">{__('Pending','give')}</option>
                    <option value="draft">{__('Draft','give')}</option>
                    <option value="trash">{__('Trash','give')}</option>
                </select>
            </div>
            {!!errors && (
                <div className={styles.updateError}>
                    {!!successes && (
                        <span>
                            {successes +
                                ' ' +
                                _n(
                                    'form was updated successfully',
                                    'forms were updated successfully.',
                                    successes,
                                    'give'
                                )}
                        </span>
                    )}
                    <span>
                        {errors + ' ' + _n("form couldn't be updated.", "forms couldn't be updated.", errors, 'give')}
                    </span>
                    <button
                        type="button"
                        className={cx('dashicons dashicons-dismiss', styles.dismiss)}
                        onClick={() => {
                            setErrors(0);
                            setSuccesses(0);
                        }}
                    >
                        <span className="give-visually-hidden">Dismiss</span>
                    </button>
                </div>
            )}
            <div className={styles.pageContent}>
                <div className={styles.pageActions}>
                    <Pagination
                        currentPage={data ? data.page : page}
                        totalPages={data ? Math.ceil(data.total / perPage) : 1}
                        disabled={false}
                        totalItems={data ? data.total : -1}
                        setPage={setPage}
                    />
                </div>
                <div role="group" aria-labelledby="giveDonationFormsTableCaption" className={styles.tableGroup}>
                    <table className={styles.table}>
                        <caption id="giveDonationFormsTableCaption" className={styles.tableCaption}>
                            {__('Donation Forms', 'give')}
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                    {__('ID', 'give')}
                                </th>
                                <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                    {__('Name', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={styles.tableColumnHeader}
                                    style={{textAlign: 'end'}}
                                >
                                    {__('Amount', 'give')}
                                </th>
                                <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                    {__('Goal', 'give')}
                                </th>
                                <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                    {__('Donations', 'give')}
                                </th>
                                <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                    {__('Revenue', 'give')}
                                </th>
                                <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                    {__('Shortcode', 'give')}
                                </th>
                                <th scope="col" aria-sort="ascending" className={styles.tableColumnHeader}>
                                    {__('Date', 'give')}
                                </th>
                                <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                    {__('Status', 'give')}
                                </th>
                            </tr>
                        </thead>
                        <tbody className={styles.tableContent}>
                            <TableRows data={data} />
                        </tbody>
                    </table>
                </div>
            </div>
        </article>
    );
}

ReactDOM.render(
    <StrictMode>
        <AdminDonationForms />
    </StrictMode>,
    document.getElementById('give-admin-donation-forms-root')
);
