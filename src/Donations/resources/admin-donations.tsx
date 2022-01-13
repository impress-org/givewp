import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import {__} from '@wordpress/i18n';
import {useEffect, useState} from 'react';

import mockDonations from './mock-donations.json';
import styles from './admin-donations.module.scss';

declare global {
    interface Window {
        GiveDonations: {apiNonce: string; apiRoot: string};
    }
}

type Donation = {
    id: number;
    amount: number;
    paymentType: string;
    datetime: string;
    donorName: string;
    donationForm: string;
    status: string;
};

async function fetchDonations(apiRoot: string, args: {} = {}) {
    let url = apiRoot + '?' + new URLSearchParams(args).toString();
    let response = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.GiveDonations.apiNonce,
        },
    });
    if (response.ok) {
        const result = await response.json();
        return result;
    } else {
        return false;
    }
}

function AdminDonations() {
    const [donations, setDonations] = useState(mockDonations);

    useEffect(() => {
        (async () => {
            const donationsResponse = await fetchDonations(window.GiveDonations.apiRoot);
            donationsResponse ? setDonations([...donationsResponse]) : setDonations([...mockDonations]);
        })();
    }, []);

    return (
        <>
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{__('Donations', 'give')}</h1>
            </div>
            <div className={styles.pageContent}>
                <div className={styles.tableContainer}>
                    <table className={styles.table}>
                        <thead>
                            <tr>
                                <th>{__('ID', 'give')}</th>
                                <th style={{textAlign: 'end'}}>{__('Amount', 'give')}</th>
                                <th>{__('Payment Type', 'give')}</th>
                                <th>{__('Date / Time', 'give')}</th>
                                <th>{__('Donor Name', 'give')}</th>
                                <th>{__('Donation Form', 'give')}</th>
                                <th>{__('Status', 'give')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {donations.map((donation: Donation) => (
                                <tr key={donation.id}>
                                    <td>{donation.id}</td>
                                    <td style={{textAlign: 'end'}}>{donation.amount}</td>
                                    <td>{donation.paymentType}</td>
                                    <td>{donation.datetime}</td>
                                    <td>{donation.donorName}</td>
                                    <td>{donation.donationForm}</td>
                                    <td>{donation.status}</td>
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
        <AdminDonations />
    </StrictMode>,
    document.getElementById('give-admin-donations-root')
);
