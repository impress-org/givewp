import {__} from "@wordpress/i18n";
import styles from "./DonorsColumns.module.scss";
import {ListTableColumn} from "@givewp/components";
import {IdBadge} from "@givewp/components/ListTable/TableCell";

export const donorsColumns: Array<ListTableColumn> = [
    {
        name: 'name',
        text: __('Name', 'give'),
        inlineSize: '12rem',
        addClass: styles.nameCell,
        render: (donor: {name, namePrefix, gravatar, id}) => (
            <>
                <div className={styles.gravatarWrapper}/>
                <img className={styles.gravatar} src={donor.gravatar} alt={`avatar for ${donor.name}`} loading='lazy'/>
                <b className={styles.name}>{donor.namePrefix ? donor.namePrefix + ' ' :''}{donor.name}</b>
                <IdBadge addClass={styles.id} id={donor.id}/>
            </>
        ),
    },
    {
        name: 'email',
        text: __('Email', 'give'),
        addClass: styles.email,
        inlineSize: '10rem',
        heading: true,
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
