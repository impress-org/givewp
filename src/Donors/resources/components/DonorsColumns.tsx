import {__} from "@wordpress/i18n";
import styles from "./DonationFormsColumns.module.scss";
import cx from "classnames";
import {ListTableColumn} from "@givewp/components";

export const donorsColumns: Array<ListTableColumn> = [
    {
        name: 'name',
        text: __('Name', 'give'),
        heading: true,
    },
    {
        name: 'email',
        text: __('Email', 'give'),
    },
    {
        name: 'donations',
        text: __('Donations', 'give'),
        render: (donor: {donationCount, id}) => {
            console.log(donor);
            return (
                <a href={`http://givewp-test-2.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&donor=${donor.id}`}>
                    {`${donor.donationCount.toString()}`}
                </a>
            )
        }
    },
    {
        name: 'donationRevenue',
        text: __('Total Donated', 'give'),
    },
    {
        name: 'dateCreated',
        text: __('Date', 'give'),
    },
];
