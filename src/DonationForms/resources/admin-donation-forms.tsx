import {StrictMode, useEffect, useState} from 'react';
import ReactDOM from 'react-dom';
import {__} from '@wordpress/i18n';
import cx from 'classnames';

import styles from './admin-donation-forms.module.scss';
import mockDonationForms from './mock-donation-forms.json';
import Pagination from './components/Pagination.js';
import Shortcode from './components/Shortcode';

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

type DonationForm = {
    id: number;
    name: string;
    amount: number | [number, number];
    goal: string | number;
    donations: number;
    revenue: number;
    datetime: string;
    shortcode: string;
};

async function fetchForms(args: {} = {}) {
    let url = window.GiveDonationForms.apiRoot + '?' + new URLSearchParams(args).toString();
    let response = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.GiveDonationForms.apiNonce,
        },
    });
    if (response.ok) {
        const result = await response.json();
        return result;
    } else {
        return false;
    }
}

function AdminDonationForms() {
    const [state, setState] = useState({
        donationForms: [...mockDonationForms],
        count: 0,
        page: 1,
    });
    const perPage = 10;

    useEffect(() => {
        (async () => {
            const donationsResponse = await fetchForms({page: state.page, perPage: perPage});
            if (donationsResponse) {
                setState((prevState) => {
                    return {
                        ...prevState,
                        donationForms: [...donationsResponse.forms],
                        count: donationsResponse.total,
                    };
                });
            } else {
                setState((prevState) => {
                    return {
                        ...prevState,
                        donationForms: [...mockDonationForms],
                        count: 2,
                    };
                });
            }
        })();
    }, [state.page]);

    function deleteForm() {
        // TODO
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
                                        <a href={`post.php?post=${form.id}&action=edit`}>{form.name}</a>
                                        <div
                                            role="group"
                                            aria-label={__('Actions', 'give')}
                                            className={styles.tableRowActions}
                                        >
                                            <a href={`post.php?post=${form.id}&action=edit`} className={styles.action}>
                                                Edit <span className="give-visually-hidden">{form.name}</span>
                                            </a>
                                            <button type="button" onClick={deleteForm} className={styles.action}>
                                                Delete <span className="give-visually-hidden">{form.name}</span>
                                            </button>
                                            <a href={`/?p=${form.id}`}>{__('View', 'give')}</a>
                                            <a href="#todo-replace-with-duplicate-link">{__('Duplicate', 'give')}</a>
                                        </div>
                                    </th>
                                    <td className={styles.tableCell} style={{textAlign: 'end'}}>
                                        {form.amount}
                                    </td>
                                    <td className={styles.tableCell}>
                                        {form.goal ? (
                                            <a
                                                href={`post.php?post=${form.id}&action=edit&give_tab=donation_goal_options`}
                                            >
                                                {form.goal}
                                            </a>
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
                                        <div className={styles.statusBadge}>Published</div>
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
