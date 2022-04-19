import {__, _n, sprintf} from "@wordpress/i18n";
import styles from "./DonorsColumns.module.scss";
import {ListTableColumn} from "@givewp/components";
import {DonorType} from "@givewp/components/ListTable/TypeBadge";

export const donorsColumns: Array<ListTableColumn> = [
    {
        name: 'id',
        text: __('ID', 'give'),
        addClass: styles.donorCell,
        inlineSize: '3rem',
        preset: 'idBadge'
    },
    {
        name: 'name',
        text: __('Donor Information', 'give'),
        addClass: styles.donorCell,
        inlineSize: '14rem',
        heading: true,
        render: (donor: {name, namePrefix, id, email, gravatar}) => (
            <div className={styles.donorInformation}>
                <img className={styles.gravatar} src={donor.gravatar} alt={donor.name} loading='lazy'/>
                <a className={styles.name} href={`/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donor.id}`}>{donor.namePrefix ? donor.namePrefix + ' ' :''}{donor.name}</a>
                <address className={styles.email}>{donor.email}</address>
            </div>
        ),
    },
    {
        name: 'donationRevenue',
        addClass: styles.donorCell,
        inlineSize: '6rem',
        text: __('Total Given', 'give'),
        preset: 'monetary',
    },
    {
        name: 'donationCount',
        text: __('Donations', 'give'),
        addClass: styles.donorCell,
        inlineSize: '8rem',
        render: (donor: {donationCount, email}) => (
            <a href={`edit.php?post_type=give_forms&page=give-payment-history&search=${donor.email}`}>
                {
                    donor.donationCount > 0 ?
                    sprintf(_n('%d donation', '%d donations', parseInt(donor.donationCount), 'give'), donor.donationCount)
                    : __('No donations', 'give')
                }
            </a>
        )
    },
    {
        name: 'latestDonation',
        text: __('Latest Donation', 'give'),
        addClass: styles.donorCell,
    },
    {
        name: 'donorType',
        text: __('Donor Type', 'give'),
        addClass: styles.donorCell,
        inlineSize: '11rem',
        render: (donor: {donorType}) => (
            <DonorType type={donor.donorType}/>
        )
    },
    {
        name: 'dateCreated',
        text: __('Date Created', 'give'),
        addClass: styles.donorCell,
    },
];
