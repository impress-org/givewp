import {__} from "@wordpress/i18n";
import styles from "./DonorsColumns.module.scss";
import {ListTableColumn} from "@givewp/components";

export const donorsColumns: Array<ListTableColumn> = [
    {
        name: 'name',
        text: __('Name', 'give'),
        inlineSize: '12rem',
        addClass: styles.nameCell,
        render: (donor: {name, namePrefix, gravatar}) => (
            <>
                <div className={styles.gravatarWrapper}/>
                <img className={styles.gravatar} src={donor.gravatar} alt={`avatar for ${donor.name}`} loading='lazy'/>
                <b className={styles.name}>{donor.namePrefix ? donor.namePrefix + ' ' :''}{donor.name}</b>
            </>
        ),
    },
    {
        name: 'email',
        text: __('Email', 'give'),
        inlineSize: '10rem',
        heading: true,
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
