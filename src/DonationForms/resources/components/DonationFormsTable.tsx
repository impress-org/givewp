import {useEffect, useState} from 'react';
import {__, _n} from '@wordpress/i18n';
import {mutate} from 'swr';
import cx from 'classnames';

import styles from './DonationFormsTable.module.scss';
import Pagination from './Pagination.js';
import loadingForms from '../loadingForms.json';
import {fetchWithArgs, useDonationForms} from '../api';

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

export enum DonationStatus {
    Any = 'any',
    Publish = 'publish',
    Pending = 'pending',
    Draft = 'draft',
    Trash = 'trash',
}

interface DonationFormsTableProps {
    statusFilter: DonationStatus;
    search: string;
}

export default function DonationFormsTable({statusFilter: status, search}: DonationFormsTableProps) {
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [errors, setErrors] = useState(0);
    const [successes, setSuccesses] = useState(0);
    const listParams = {
        page,
        perPage,
        status,
        search,
    };
    const {data, error} = useDonationForms(listParams);
    const isEmpty = !error && data?.forms.length === 0;
    useEffect(() => setPage(1), [status, search]);

    async function mutateForm(ids, endpoint, method) {
        try {
            const response = await fetchWithArgs(endpoint, {ids}, method);
            // if we just removed the last entry from the page and we're not on the first page, go back a page
            if( !response.errors && data.forms.length == 1 && data.forms.totalPages > 1
                && (endpoint == '/delete' || endpoint == '/trash' || endpoint == '/restore') )
            {
                setPage(page - 1);
            }
            // otherwise, revalidate current page
            else
            {
                await mutate(listParams, data);
            }
            //revalidate all pages after the current page and null their data
            const mutations = [];
            for (let i = response.page + 1; i <= Math.ceil(data.total / perPage); i++) {
                mutations.push(mutate({...listParams, page: i}, null));
            }
            setErrors(response.errors);
            setSuccesses(response.successes);
        } catch (error) {
            return error;
        }
    }

    function deleteForm(event) {
        const endpoint = data.trash ? '/trash' : '/delete';
        mutateForm(event.target.dataset.formid, endpoint, 'DELETE');
    }

    function duplicateForm(event) {
        mutateForm(event.target.dataset.formid, '/duplicate', 'POST');
    }

    function restoreForm(event) {
        mutateForm(event.target.dataset.formid, '/restore', 'POST');
    }

    function TableRows({data}) {
        const forms = data ? data.forms : loadingForms;
        const trash = data ? data.trash : false;

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

    return (
        <>
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
            <div className={styles.pageActions}>
                <Pagination
                    currentPage={data ? data.page : page}
                    totalPages={data ? Math.ceil(data.total / perPage) : 1}
                    disabled={false}
                    totalItems={data ? data.total : -1}
                    setPage={setPage}
                />
            </div>
            <div
                role="group"
                aria-labelledby="giveDonationFormsTableCaption"
                aria-describedby="giveDonationFormsTableMessage"
                className={styles.tableGroup}
            >
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
                <div id="giveDonationFormsTableMessage">
                    {isEmpty && <div className={styles.statusMessage}>{__('No donation forms found.', 'give')}</div>}
                </div>
            </div>
        </>
    );
}
