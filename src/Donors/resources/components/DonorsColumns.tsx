import {__} from '@wordpress/i18n';
import {ListTableColumn} from '@givewp/components';

//ToDo Delete File in favor of window object
export const donorsColumns: Array<ListTableColumn> = [
    {
        name: 'id',
        text: __('ID', 'give'),
        isSortable: true,
    },
    {
        name: 'name',
        text: __('Donor Information', 'give'),
        isSortable: true,
    },
    {
        name: 'donationRevenue',
        text: __('Total Given', 'give'),
        isSortable: false,
    },
    {
        name: 'donationCount',
        text: __('Donations', 'give'),
        isSortable: true,
    },
    {
        name: 'latestDonation',
        text: __('Latest Donation', 'give'),
        isSortable: true,
    },
    {
        name: 'donorType',
        text: __('Donor Type', 'give'),
        isSortable: true,
    },
    {
        name: 'dateCreated',
        text: __('Date Created', 'give'),
        isSortable: true,
    },
];
