import {StrictMode, SyntheticEvent} from 'react';
import ReactDOM from 'react-dom';
import {sprintf, __} from '@wordpress/i18n';
import {useEffect, useState} from 'react';

import {Button, Checkbox} from './components';
import mockDonations from './mock-donations.json';
import styles from './admin-donations.module.scss';

type Donation = {
    id: number;
    amount: number;
    paymentType: string;
    datetime: string;
    donorName: string;
    donationForm: string;
    status: string;
};

function handleSubmit(event: SyntheticEvent<HTMLFormElement, SubmitEvent> & {target: HTMLFormElement}) {
    event.preventDefault();

    console.log(new FormData(event.target).getAll('donation'));
}

async function fetchDonations( args = {} ) {
    let url = '/wp-json/give-api/v2/donations/?';
    url += new URLSearchParams( args ).toString();
    let response = await fetch( url, {
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.GiveDonations.apiNonce,
        }
    })
    if( response.ok ) {
        const result = await response.json();
        console.log(result);
        return result;
    }
    else {
        return false;
    }
}

function AdminDonations() {
    const [donations, setDonations] = useState(mockDonations);

    useEffect( () => {
        ( async () => {
            const donationsResponse = await fetchDonations();
            donationsResponse ? setDonations(donationsResponse) : setDonations(mockDonations);
        })()
    }, []);

    return (
        <>
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{__('Donations', 'give')}</h1>
            </div>
            <div className={styles.pageContent}>
                <form onSubmit={handleSubmit}>
                    <Button type="submit">Submit</Button>
                    <div className={styles.tableContainer}>
                        <table className={styles.table}>
                            <thead>
                                <tr>
                                    <th>
                                        <label htmlFor="all" className={styles.visuallyHidden}>
                                            {__('Select All Donations', 'give')}
                                        </label>
                                        <Checkbox id="all" name="all" />
                                    </th>
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
                                        <td>
                                            <label
                                                htmlFor={`donation-${donation.id}`}
                                                className={styles.visuallyHidden}
                                            >
                                                {__(sprintf('Donation %d', donation.id), 'give')}
                                            </label>
                                            <Checkbox
                                                id={`donation${donation.id}`}
                                                name="donation"
                                                value={donation.id}
                                            />
                                            <div className={styles.rowActions}>
                                                <a href={"#edit"}>{__('Edit', 'give')}</a>
                                                <a href="#delete">{__('Delete', 'give')}</a>
                                            </div>
                                        </td>
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
                </form>
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
