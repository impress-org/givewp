import {StrictMode, useEffect, useState} from 'react';
import ReactDOM from 'react-dom';
import {__} from '@wordpress/i18n';
import cx from 'classnames';

import styles from './admin-donation-forms.module.scss';
import Pagination from './components/Pagination.js';
import Shortcode from './components/Shortcode';
import API, {useFetcher, getEndpoint} from './api';

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

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
    const [state, setState] = useState({
        donationForms: [],
        count: 0,
        page: 1,
        trash: true,
    });
    const perPage = 10;

    const parameters = {
        page: state.page,
        perPage: perPage,
    };

    const {data, isLoading, isError} = useFetcher(getEndpoint('', parameters), {
        onSuccess: ({response}) => {
            setState((previousState) => {
                return {
                    ...previousState,
                    page: response.page,
                    count: response.total,
                    trash: response.trash,
                    donationForms: response.forms,
                };
            });
        },
    });

    function deleteForm(event) {
        const endpoint = state.trash ? '/trash' : '/delete';
        API.delete(endpoint, {params: {...parameters, ids: event.target.dataset.formid}})
            .then((response) => {
                setState((previousState) => {
                    return {
                        ...previousState,
                        page: response.data.page,
                        count: response.data.total,
                        trash: response.data.trash,
                        donationForms: response.data.forms,
                    };
                });
            })
            .catch((error) => {
            });
    }

    return (
        <article>
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{__('Donation Forms', 'give')}</h1>
                <a href="post-new.php?post_type=give_forms" className={styles.button}>
                    Add Form
                </a>
            </div>
            <div className={styles.pageContent}>
                <nav className={styles.paginationContainer}>
                    <span className={styles.totalItems}>{state.count.toString() + __(' forms', 'give')}</span>
                    <Pagination
                        currentPage={state.page}
                        totalPages={Math.ceil(state.count / perPage)}
                        disabled={false}
                        setPage={(page) => {
                            setState((prevState) => {
                                return {
                                    ...prevState,
                                    page: page,
                                };
                            });
                        }}
                    />
                </nav>
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
                            {state.donationForms.map((form) => (
                                <tr key={form.id} className={styles.tableRow}>
                                    <td className={styles.tableCell}>
                                        <div className={styles.idBadge}>{form.id}</div>
                                    </td>
                                    <th className={cx(styles.tableCell, styles.tableRowHeader)} scope="row">
                                        <a href={form.edit}>{form.name}</a>
                                        <div
                                            role="group"
                                            aria-label={__('Actions', 'give')}
                                            className={styles.tableRowActions}
                                        >
                                            <a href={form.edit} className={styles.action}>
                                                Edit <span className="give-visually-hidden">{form.name}</span>
                                            </a>
                                            <button type="button" onClick={deleteForm} data-formid={form.id} className={styles.action}>
                                                {state.trash ? __('Trash', 'give') : __('Delete', 'give')}{' '}
                                                <span className="give-visually-hidden">{form.name}</span>
                                            </button>
                                            <a href={form.permalink}>{__('View', 'give')}</a>
                                            <a href="#todo-replace-with-duplicate-link">{__('Duplicate', 'give')}</a>
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
                                                <a
                                                    href={`post.php?post=${form.id}&action=edit&give_tab=donation_goal_options`}
                                                >
                                                    {form.goal.actual}
                                                </a>
                                                {form.goal.goal ? (
                                                    <span>
                                                        {' '}
                                                        {__('of', 'give')} {form.goal.goal}{' '}
                                                        {form.goal.format != 'amount' ? form.goal.format : null}
                                                    </span>
                                                ) : null}
                                            </>
                                        ) : (
                                            'No Goal Set'
                                        )}
                                    </td>
                                    <td className={styles.tableCell}>
                                        <a
                                            href={`edit.php?post_type=give_forms&page=give-payment-history&form_id=${form.id}`}
                                        >
                                            {form.donations}
                                        </a>
                                    </td>
                                    <td className={styles.tableCell}>
                                        <a
                                            href={`edit.php?post_type=give_forms&page=give-reports&tab=forms&form-id=${form.id}`}
                                        >
                                            {form.revenue}
                                        </a>
                                    </td>
                                    <td className={styles.tableCell}>
                                        <Shortcode code={form.shortcode} />
                                    </td>
                                    <td className={styles.tableCell}>{form.datetime}</td>
                                    <td className={styles.tableCell}>
                                        <div className={styles.statusBadge}>{form.status}</div>
                                    </td>
                                </tr>
                            ))}
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
