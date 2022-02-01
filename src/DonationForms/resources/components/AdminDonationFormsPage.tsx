import {useState} from 'react';
import type {ChangeEventHandler} from 'react';
import {__} from '@wordpress/i18n';

import styles from '../admin-donation-forms.module.scss';
import DonationFormsTable, {DonationStatus} from './DonationFormsTable';

function getDonationStatusText(donationStatus: DonationStatus): string {
    switch (donationStatus) {
        case DonationStatus.Any:
            return __('All', 'give');
        case DonationStatus.Publish:
            return __('Published', 'give');
        case DonationStatus.Pending:
            return __('Pending', 'give');
        case DonationStatus.Draft:
            return __('Draft', 'give');
        case DonationStatus.Trash:
            return __('Trash', 'give');
    }
}

export default function AdminDonationFormsPage() {
    const [statusFilter, setStatusFilter] = useState<DonationStatus>(DonationStatus.Any);
    const [search, setSearch] = useState<string>('');
    const handleStatusFilterChange: ChangeEventHandler<HTMLSelectElement> = (event) =>
        setStatusFilter(event.target.value as DonationStatus);
    const handleSearchSubmit = (event) => {
        event.preventDefault();
        setSearch(event.target.searchInput.value);
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
                <form onSubmit={handleSearchSubmit}>
                    <input className={styles.textInput} name='searchInput' type='text' placeholder={__('Search by name or ID', 'give')}/>
                    <button className={styles.button}>
                        {__('Search', 'give')}
                    </button>
                </form>
                <select onChange={handleStatusFilterChange}>
                    {Object.values(DonationStatus).map((donationStatus) => (
                        <option key={donationStatus} value={donationStatus}>
                            {getDonationStatusText(donationStatus)}
                        </option>
                    ))}
                </select>
            </div>
            <div className={styles.pageContent}>
                <DonationFormsTable statusFilter={statusFilter} search={search} />
            </div>
        </article>
    );
}
