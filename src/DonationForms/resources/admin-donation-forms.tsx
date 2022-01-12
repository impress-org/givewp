import {StrictMode, SyntheticEvent} from 'react';
import ReactDOM from 'react-dom';
import {sprintf, __} from '@wordpress/i18n';

import styles from './admin-donation-forms.module.scss';
import mockDonationForms from './mock-donation-forms.json';
import {Button, Checkbox} from "../../Donations/resources/components";
import {useSelectAll} from "../../Donations/resources/hooks";

declare global {
    interface Window { GiveDonationForms: {apiNonce: string, apiRoot: string}; }
}

type DonationForm = {
    id: number,
    name: string,
    amount: number | [number, number],
    goal: string | number,
    donations: number,
    datetime: string,
    shortcode: string,
};

function handleSubmit(event: SyntheticEvent<HTMLFormElement, SubmitEvent> & {target: HTMLFormElement}) {
    event.preventDefault();

    console.log(new FormData(event.target).getAll('donation-form'));
}

function AdminDonationForms() {
    const selectAllRef = useSelectAll('donation-form');
    return (
        <>
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{__('Donation Forms', 'give')}</h1>
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
                                    <Checkbox ref={selectAllRef} id="all" name="all" />
                                </th>
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
                                    <td>
                                        <label
                                            htmlFor={`donation-form-${form.id}`}
                                            className={styles.visuallyHidden}
                                        >
                                            {form.name}
                                        </label>
                                        <Checkbox
                                            id={`donation-form-${form.id}`}
                                            name="donation-form"
                                            value={form.id}
                                        />
                                        <div className={styles.rowActions}>
                                            <a href={"#edit"}>{__('Edit', 'give')}</a>
                                            <a href="#delete">{__('Delete', 'give')}</a>
                                        </div>
                                    </td>
                                    <td>{form.name}</td>
                                    <td style={{textAlign: 'end'}}>{form.amount}</td>
                                    <td>{form.goal ? form.goal : "No Goal Set"}</td>
                                    <td>{form.donations}</td>
                                    <td>{form.shortcode}</td>
                                    <td>{form.datetime}</td>
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
        <AdminDonationForms />
    </StrictMode>,
    document.getElementById('give-admin-donation-forms-root')
);
