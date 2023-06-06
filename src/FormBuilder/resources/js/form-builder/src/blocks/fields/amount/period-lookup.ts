import {__} from "@wordpress/i18n";

const periodLookup = {
    'one-time': {
        singular: __('one-time', 'give'),
        plural: __('one-time', 'give'),
        adjective: __('Once', 'give'),
    },
    day: {
        singular: __('day', 'give'),
        plural: __('days', 'give'),
        adjective: __('Daily', 'give'),
    },
    week: {
        singular: __('week', 'give'),
        plural: __('weeks', 'give'),
        adjective: __('Weekly', 'give'),
    },
    month: {
        singular: __('month', 'give'),
        plural: __('months', 'give'),
        adjective: __('Monthly', 'give'),
    },
    quarter: {
        singular: __('quarter', 'give'),
        plural: __('quarters', 'give'),
        adjective: __('Quarterly', 'give'),
    },
    year: {
        singular: __('year', 'give'),
        plural: __('years', 'give'),
        adjective: __('Yearly', 'give'),
    },
};

export default periodLookup;
