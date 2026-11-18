import {__} from '@wordpress/i18n';
import {subDays} from 'date-fns';
import type {FilterByGroupedOptions} from '../ListTablePage/types';

/**
 * The "Period" group of the "Filter by" menu, shared by the screens that filter by date. The URL keeps
 * the stable number of days (`7`, `30`, `90`) so a link stays valid on reload; the endpoint receives
 * the ISO date that many days before the request time.
 *
 * @since TBD
 */
export function periodFilterGroup(): FilterByGroupedOptions {
    return {
        id: 'period',
        apiParam: 'start',
        name: __('Period', 'give'),
        type: 'radio',
        toApiValue: (days, now) => subDays(now, Number(days)).toISOString(),
        options: [
            {text: __('Last 90 days', 'give'), value: '90'},
            {text: __('Last 30 days', 'give'), value: '30'},
            {text: __('Last 7 days', 'give'), value: '7'},
        ],
    };
}
