import {__, _n, sprintf} from "@wordpress/i18n";
import styles from "./DonorsColumns.module.scss";
import {ListTableColumn} from "@givewp/components";
import cx from "classnames";

export const donorsColumns: Array<ListTableColumn> = [
    {
        name: 'id',
        text: __('ID', 'give'),
        inlineSize: '3rem',
        preset: 'idBadge'
    },
    {
        name: 'name',
        text: __('Donor Information', 'give'),
        inlineSize: '14rem',
        alignColumn: 'start',
        heading: true,
        render: (donor: {name, namePrefix, id, email, gravatar}) => (
            <div className={styles.donorInformation}>
                <img className={styles.gravatar} src={donor.gravatar} alt={donor.name} loading='lazy'/>
                <strong className={styles.name}>{donor.namePrefix ? donor.namePrefix + ' ' :''}{donor.name}</strong>
                <a className={styles.email} href={`mailto:${donor.email}`}>{donor.email}</a>
            </div>
        ),
    },
    {
        name: 'donationRevenue',
        inlineSize: '6rem',
        text: __('Total Given', 'give'),
        preset: 'monetary'
    },
    {
        name: 'donationCount',
        text: __('Donations', 'give'),
        inlineSize: '8rem',
        render: (donor: {donationCount, id}) => (
            <a href={`edit.php?post_type=give_forms&page=give-payment-history&donor=${donor.id}`}>
                {
                    donor.donationCount > 0 ?
                    sprintf(_n('%d donation', '%d donations', parseInt(donor.donationCount), 'give'), donor.donationCount)
                    : __('No donations', 'give')
                }
            </a>
        )
    },
    {
        name: 'dateCreated',
        text: __('Date Created', 'give'),
    },
];
