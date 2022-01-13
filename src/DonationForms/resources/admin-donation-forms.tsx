import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import {__} from '@wordpress/i18n';

import styles from './admin-donation-forms.module.scss';
import mockDonationForms from './mock-donation-forms.json';

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
    datetime: string;
    shortcode: string;
};

function AdminDonationForms() {
    return (
        <>
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{__('Donation Forms', 'give')}</h1>
            </div>
            <div className={styles.pageContent}>
                <div className={styles.tableContainer}>
                    <table className={styles.table}>
                        <thead>
                            <tr>
                                <th>{__('Name', 'give')}</th>
                                <th style={{textAlign: 'end'}}>{__('Amount', 'give')}</th>
                                <th>{__('Goal', 'give')}</th>
                                <th>{__('Donations', 'give')}</th>
                                <th>{__('Shortcode', 'give')}</th>
                                <th>{__('Date', 'give')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {mockDonationForms.map((form: DonationForm) => (
                                <tr key={form.id}>
                                    <td>{form.name}</td>
                                    <td style={{textAlign: 'end'}}>{form.amount}</td>
                                    <td>{form.goal ? form.goal : 'No Goal Set'}</td>
                                    <td>{form.donations}</td>
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
