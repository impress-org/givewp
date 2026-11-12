import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import type {ListTableItem, ListTableRowAction} from '@givewp/components/ListTable/ListTablePage';

/* Read at load: the "Create event" modal later rewrites `apiRoot` on the shared window object. */
const API_ROOT = window.GiveEventTickets.apiRoot;

const salesCount = (item: ListTableItem) => parseInt(String(item.salesCount ?? '').match(/^\d+/)?.[0] ?? '0', 10);

/**
 * Event actions: "Edit" inline, "Delete" in the row menu and the selection bar. An event with ticket
 * sales cannot be deleted; trying explains why in an error notice.
 *
 * @since TBD Declare the row actions as data for the list engine; report errors on the page.
 */
export const eventTicketsRowActions: ListTableRowAction[] = [
    {
        id: 'edit',
        label: __('Edit', 'give'),
        isPrimary: true,
        href: (item) => `edit.php?post_type=give_forms&page=give-event-tickets&id=${item.id}`,
    },
    {
        id: 'delete',
        label: __('Delete', 'give'),
        isDestructive: true,
        supportsBulk: true,
        confirm: {
            title: __('Delete events', 'give'),
            body: () => <p>{__('Really delete the following events?', 'give')}</p>,
            confirmLabel: __('Delete', 'give'),
        },
        run: async (items) => {
            if (items.some((item) => salesCount(item) > 0)) {
                throw new Error(__('An event cannot be deleted while it has donations associated with it.', 'give'));
            }

            return apiFetch({
                url: addQueryArgs(API_ROOT, {ids: items.map((item) => item.id).join(',')}),
                method: 'DELETE',
            });
        },
    },
];
