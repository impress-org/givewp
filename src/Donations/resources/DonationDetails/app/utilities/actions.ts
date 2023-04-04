import {__} from '@wordpress/i18n';

/**
 *
 * @unreleased
 */
export const actions: Array<{title: string; action: () => void}> = [
    {title: __('Refund donation', 'give'), action: () => alert('refund donation')},
    {title: __('Download receipt', 'give'), action: () => alert('Download receipt')},
    {title: __('Resend receipt', 'give'), action: () => alert('Resend receipt')},
    {title: __('Delete donation', 'give'), action: () => alert('Delete donation')},
];
