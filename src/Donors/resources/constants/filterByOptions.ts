import { FilterByGroupedOptions } from "@givewp/components/ListTable/ListTablePage";
import { __ } from "@wordpress/i18n";

const donorStatuses = window.GiveDonors.donorStatuses || {};

const filterByOptions: Array<FilterByGroupedOptions> = [
    {
        id: 'status',
        name: __('Status', 'give'),
        type: 'radio',
        options: Object.entries(donorStatuses).map(([value, text]) => ({ text, value })),
        defaultValue: 'active',
    },
    {
        id: 'start',
        name: __('Period', 'give'),
        type: 'radio',
        options: [
            { text: __('Last 90 days', 'give'), value: '90d' },
            { text: __('Last 30 days', 'give'), value: '30d' },
            { text: __('Last 7 days', 'give'), value: '7d' },
        ]
    }
];

export default filterByOptions;
