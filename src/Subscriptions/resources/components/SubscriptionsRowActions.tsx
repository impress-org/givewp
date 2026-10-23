import {__} from '@wordpress/i18n';
import ListTableApi from '@givewp/components/ListTable/api';
import SetStatusDialog from '@givewp/components/ListTable/SetStatusDialog';
import type {ListTableItem, ListTableParameters, ListTableRowAction} from '@givewp/components/ListTable/ListTablePage';

const subscriptionsApi = new ListTableApi(window.GiveSubscriptions);

const idsOf = (items: ListTableItem[]) => items.map((item) => item.id).join(',');

const isTrashView = (parameters: ListTableParameters) => [].concat(parameters?.status ?? []).includes('trashed');

/**
 * The statuses an admin can set from the list, in the order the old "Set To …" actions had.
 */
const SUBSCRIPTION_STATUSES = [
    {value: 'active', label: __('Active', 'give')},
    {value: 'expired', label: __('Expired', 'give')},
    {value: 'completed', label: __('Completed', 'give')},
    {value: 'cancelled', label: __('Cancelled', 'give')},
    {value: 'pending', label: __('Pending', 'give')},
    {value: 'failing', label: __('Failing', 'give')},
    {value: 'suspended', label: __('Suspended', 'give')},
    {value: 'abandoned', label: __('Abandoned', 'give')},
];

/**
 * Subscription actions: "Edit" inline ("Restore" in the trash view); "Trash", "Set status" (or
 * "Delete permanently" in the trash view) in the row menu. All but "Edit" also run on a selection.
 *
 * @since TBD Declare the actions as data for the list engine; one "Set status" replaces the "Set To …" bulk actions.
 */
export const subscriptionsRowActions: ListTableRowAction[] = [
    {
        id: 'edit',
        label: __('Edit', 'give'),
        isPrimary: true,
        isVisible: (data, parameters) => !isTrashView(parameters),
        href: (item) => `edit.php?post_type=give_forms&page=give-subscriptions&id=${item.id}`,
    },
    {
        id: 'restore',
        label: __('Restore', 'give'),
        isPrimary: true,
        supportsBulk: true,
        isVisible: (data, parameters) => isTrashView(parameters),
        confirm: {
            title: __('Restore subscriptions', 'give'),
            body: () => <p>{__('Are you sure you want remove from trash the following subscriptions?', 'give')}</p>,
            confirmLabel: __('Restore', 'give'),
        },
        run: (items) => subscriptionsApi.fetchWithArgs('/untrash', {ids: idsOf(items)}, 'POST'),
    },
    {
        id: 'trash',
        label: __('Trash', 'give'),
        supportsBulk: true,
        isVisible: (data, parameters) => !isTrashView(parameters),
        confirm: {
            title: __('Trash subscriptions', 'give'),
            body: () => <p>{__('Are you sure you want add to trash the following subscriptions?', 'give')}</p>,
            confirmLabel: __('Move to trash', 'give'),
        },
        run: (items) => subscriptionsApi.fetchWithArgs('/trash', {ids: idsOf(items)}, 'DELETE'),
    },
    {
        id: 'set-status',
        label: __('Set status', 'give'),
        supportsBulk: true,
        isVisible: (data, parameters) => !isTrashView(parameters),
        Dialog: (props) => (
            <SetStatusDialog
                {...props}
                onSetStatus={(ids, status) =>
                    subscriptionsApi.fetchWithArgs('/setStatus', {ids: ids.join(','), status}, 'POST')
                }
                message={<p>{__('Set status for the following subscriptions?', 'give')}</p>}
                statuses={SUBSCRIPTION_STATUSES}
            />
        ),
    },
    {
        id: 'delete',
        label: __('Delete permanently', 'give'),
        isDestructive: true,
        supportsBulk: true,
        isVisible: (data, parameters) => isTrashView(parameters),
        confirm: {
            title: __('Delete subscriptions', 'give'),
            body: () => <p>{__('Really delete the following subscriptions?', 'give')}</p>,
            confirmLabel: __('Delete', 'give'),
        },
        run: (items) => subscriptionsApi.fetchWithArgs('/delete', {ids: idsOf(items)}, 'DELETE'),
    },
];
