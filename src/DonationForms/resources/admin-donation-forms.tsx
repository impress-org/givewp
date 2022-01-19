import {StrictMode, SyntheticEvent, useEffect, useState} from 'react';
import ReactDOM from 'react-dom';
import {__} from '@wordpress/i18n';

import styles from './admin-donation-forms.module.scss';
import mockDonationForms from './mock-donation-forms.json';
import Pagination from './components/Pagination.js';

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
        }
    })
    if (response.ok) {
        const result = await response.json();
        console.log(result);
        return result;
    } else {
        return false;
    }
}

function AdminDonationForms() {
    const [state, setState] = useState({
        donationForms: [...mockDonationForms],
        count: 0,
        page: 1
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
                        count: donationsResponse.total
                    }
                });
            } else {
                setState((prevState) => {
                    return {
                        ...prevState,
                        donationForms: [...mockDonationForms],
                        count: 2
                    }
                });
            }
        })()
    }, [state.page]);

    function handleSubmit(event: SyntheticEvent<HTMLFormElement, SubmitEvent> & { target: HTMLFormElement }) {
        event.preventDefault();
        setState((prevState) => {
            return {
                ...prevState,
                page: parseInt(event.target.currentPageSelector.value)
            }
        });
    }

    return (
        <>
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{__('Donation Forms', 'give')}</h1>
            </div>
            <div className={styles.pageContent}>
                <form onSubmit={handleSubmit}>
                    <button type="submit">Submit</button>
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
                                        page: page
                                    }
                                });
                            }}
                        />
                    </nav>
                </form>
                <div className={styles.tableContainer}>
                    <table className={styles.table}>
                        <thead>
                        <tr>
                            <th>{__('Name', 'give')}</th>
                            <th style={{textAlign: 'end'}}>{__('Amount', 'give')}</th>
                            <th>{__('Goal', 'give')}</th>
                            <th>{__('Donations', 'give')}</th>
                            <th>{__('Revenue', 'give')}</th>
                            <th>{__('Shortcode', 'give')}</th>
                            <th>{__('Date', 'give')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {state.donationForms.map((form) => (
                            <tr key={form.id}>
                                <td>{form.name}</td>
                                <td style={{textAlign: 'end'}}>{form.amount}</td>
                                <td>{form.goal ? form.goal : 'No Goal Set'}</td>
                                <td>{form.donations}</td>
                                <td>{form.revenue}</td>
                                <td>{form.shortcode}</td>
                                <td>{form.datetime}</td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

ReactDOM.render(
    <StrictMode>
        <AdminDonationForms />
    </StrictMode>,
    document.getElementById('give-admin-donation-forms-root')
);
