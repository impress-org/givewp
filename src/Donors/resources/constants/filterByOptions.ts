import { FilterByGroupedOptions } from "@givewp/components/ListTable/ListTablePage";
import { __ } from "@wordpress/i18n";

const filterByOptions: Array<FilterByGroupedOptions> = [
    {
        id: 'showTrashed',
        apiParam: 'status',
        name: __('Status', 'give'),
        type: 'toggle',
        options: [{ text: __('Show trashed', 'give'), value: 'trash' }],
    },
    {
        id: 'status',
        apiParam: 'status',
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
