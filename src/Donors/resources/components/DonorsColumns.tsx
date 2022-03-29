import {__} from "@wordpress/i18n";
import styles from "./DonorsColumns.module.scss";
import {ListTableColumn} from "@givewp/components";

export const donorsColumns: Array<ListTableColumn> = [
    {
        name: 'id',
        text: __('ID', 'give'),
        inlineSize: '3rem',
        preset: 'idBadge'
    },
    {
        name: 'gravatar',
        text: __('Avatar'),
        inlineSize: '6rem',
        render: (donor: {gravatar, name}) => (
            <>
                <div role='img' aria-label={`avatar for ${donor.name}`}>
                    <img className={styles.gravatar} src={donor.gravatar} alt={donor.name} loading='lazy'/>
                    <div className={styles.gravatarWrapper}/>
                </div>
            </>
        ),
    },
    {
        name: 'name',
        text: __('Name', 'give'),
        inlineSize: '12rem',
        heading: true,
        addClass: styles.nameCell,
        render: (donor: {name, namePrefix, id}) => (
                <strong className={styles.name}>{donor.namePrefix ? donor.namePrefix + ' ' :''}{donor.name}</strong>
        ),
    },
    {
        name: 'email',
        text: __('Email', 'give'),
        addClass: styles.email,
        inlineSize: '10rem',
        render: (donor: {email}) => (
          <a href={`mailto:${donor.email}`}>{donor.email}</a>
        ),
    },
    {
        name: 'donationCount',
        text: __('Donations', 'give'),
        inlineSize: '6rem',
        render: (donor: {donationCount, id}) => (
            <a href={`http://givewp-test-2.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&donor=${donor.id}`}>
                {`${donor.donationCount.toString()}`}
            </a>
        )
    },
    {
        name: 'donationRevenue',
        inlineSize: '6rem',
        text: __('Total Donated', 'give'),
    },
    {
        name: 'dateCreated',
        text: __('Date', 'give'),
    },
];
