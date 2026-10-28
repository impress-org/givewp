import {FilterByGroupedOptions} from '@givewp/components/ListTable/ListTablePage';
import {__} from '@wordpress/i18n';
import {periodFilterGroup} from '@givewp/components/ListTable/adapters/periodFilterGroup';

const donationStatuses = window.GiveDonations.donationStatuses || {};

const filterByOptions: Array<FilterByGroupedOptions> = [
    {
        id: 'showTrashed',
        apiParam: 'status',
        name: __('Status', 'give'),
        type: 'toggle',
        options: [{text: __('Show trashed', 'give'), value: 'trash'}],
    },
    {
        id: 'status',
        apiParam: 'status',
        name: __('Status', 'give'),
        showTitle: false,
        type: 'checkbox',
        isVisible: (values) => !values?.status?.includes('trash'),
        options: Object.entries(donationStatuses)
            .filter(([value]) => value !== 'trash')
            .map(([value, text]) => ({text, value})),
    },
    periodFilterGroup(),
];

export default filterByOptions;
