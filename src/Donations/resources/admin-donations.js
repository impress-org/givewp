import ReactDOM from 'react-dom';
import {sprintf, __} from '@wordpress/i18n';
import {useEffect, useState} from 'react';

import {Button, Checkbox} from './components';
import {useSelectAll} from './hooks';
//import donations from './mock-donations.json';
import styles from './admin-donations.module.scss';

function handleSubmit(event) {
    event.preventDefault();

    console.log(new FormData(event.target).getAll('donation'));
}

async function fetchDonations( args = {} ) {
    let uri = '/wp-json/give-api/v2/donations/?';
    uri += new URLSearchParams( args ).toString();
    let response = await fetch( uri, {
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.GiveDonations.apiNonce,
        }
    })
    const result = await response.json();
    console.log( result );
    return result;
    //.catch(err => console.log(err.message));
}

function AdminDonations() {
    //const selectAllRef = useSelectAll('donation');
    const [donations, setDonations] = useState([]);

    useEffect( () => {
        ( async () => {
            const donationsResult = await fetchDonations();
            setDonations(donationsResult);
        })()
    }, [])

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
                                    <th styles={{textAlign: 'end'}}>{__('Amount', 'give')}</th>
                                    <th>{__('Payment Type', 'give')}</th>
                                    <th>{__('Date / Time', 'give')}</th>
                                    <th>{__('Donor Name', 'give')}</th>
                                    <th>{__('Donation Form', 'give')}</th>
                                    <th>{__('Status', 'give')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {donations.map((donation) => (
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
                                        <td>{decodeURIComponent(donation.amount)}</td>
                                        <td>{donation.paymentType}</td>
                                        <td>{donation.datetime}</td>
                                        <td>{donation.donorName}</td>
                                        <td>{donation.donationForm}</td>
                                        <td>{donation.donationStatus}</td>
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

ReactDOM.render(<AdminDonations />, document.getElementById('give-admin-donations-root'));
