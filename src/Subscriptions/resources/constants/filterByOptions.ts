import { FilterByGroupedOptions } from "@givewp/components/ListTable/ListTablePage";
import { __ } from "@wordpress/i18n";
import { subDays } from "date-fns";

const subscriptionStatuses = window.GiveSubscriptions.subscriptionStatuses || {};

const filterByOptions: Array<FilterByGroupedOptions> = [
    {
        id: 'showTrashed',
        apiParam: 'status',
        name: __('Status', 'give'),
        type: 'toggle',
        options: [{ text: __('Show trashed', 'give'), value: 'trashed' }],
    },
    {
        id: 'status',
        apiParam: 'status',
        name: __('Status', 'give'),
        showTitle: false,
        type: 'checkbox',
        isVisible: (values) => !values?.status?.includes('trashed'),
        options: Object.entries(subscriptionStatuses)
            .filter(([value]) => value !== 'trashed')
            .map(([value, text]) => ({ text, value })),
    },
    {
        id: 'period',
        apiParam: 'start',
        name: __('Period', 'give'),
        type: 'radio',
        options: [
            { text: __('Last 90 days', 'give'), value: subDays(new Date(), 90).toISOString() },
            { text: __('Last 30 days', 'give'), value: subDays(new Date(), 30).toISOString() },
            { text: __('Last 7 days', 'give'), value: subDays(new Date(), 7).toISOString() },
        ]
    }
];

export default filterByOptions;
