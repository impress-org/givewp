import {__} from '@wordpress/i18n';
import {ListTableColumn} from '@givewp/components';

//ToDo Delete File in favor of window object
export const donorsColumns: Array<ListTableColumn> = [
    {
        id: 'id',
        label: __('ID', 'give'),
        sortable: true,
    },
    {
        id: 'name',
        label: __('Donor Information', 'give'),
        sortable: true,
    },
    {
        id: 'donationRevenue',
        label: __('Total Given', 'give'),
        sortable: false,
    },
    {
        id: 'donationCount',
        label: __('Donations', 'give'),
        sortable: true,
    },
    {
        id: 'latestDonation',
        label: __('Latest Donation', 'give'),
        sortable: true,
    },
    {
        id: 'donorType',
        label: __('Donor Type', 'give'),
        sortable: true,
    },
    {
        id: 'dateCreated',
        label: __('Date Created', 'give'),
        sortable: true,
    },
];
