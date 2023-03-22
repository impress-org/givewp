import {__} from '@wordpress/i18n';

/**
 *
 * @unreleased
 */
export const donationStatusOptions: Array<{value: string; label: string}> = [
    {
        value: 'publish',
        label: __('Completed', 'give'),
    },
    {
        value: 'pending',
        label: __('Pending', 'give'),
    },
    {
        value: 'processing',
        label: __('Processing', 'give'),
    },
    {
        value: 'refunded',
        label: __('Refunded', 'give'),
    },
    {
        value: 'revoked',
        label: __('Revoked', 'give'),
    },
    {
        value: 'failed',
        label: __('Failed', 'give'),
    },
    {
        value: 'cancelled',
        label: __('Cancelled', 'give'),
    },
    {
        value: 'abandoned',
        label: __('Abandoned', 'give'),
    },
    {
        value: 'preApproval',
        label: __('Pre-approved', 'give'),
    },
];

