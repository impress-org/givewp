import {__} from '@wordpress/i18n';
import ListTableApi from '@givewp/components/ListTable/api';
import {hasStatus} from '@givewp/components/ListTable/adapters/hasStatus';
import {setStatusRowAction} from '@givewp/components/ListTable/rowActions/setStatusRowAction';
import {trashRowActions} from '@givewp/components/ListTable/rowActions/trashRowActions';
import type {ListTableParameters, ListTableRowAction} from '@givewp/components/ListTable/ListTablePage';

const subscriptionsApi = new ListTableApi(window.GiveSubscriptions);

const isTrashView = (parameters: ListTableParameters) => hasStatus(parameters, 'trashed');

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
    ...trashRowActions({
        api: subscriptionsApi,
        isTrashView,
        editHref: (item) => `edit.php?post_type=give_forms&page=give-subscriptions&id=${item.id}`,
        text: {
            restoreTitle: __('Restore subscriptions', 'give'),
            restoreBody: __('Are you sure you want remove from trash the following subscriptions?', 'give'),
            trashTitle: __('Trash subscriptions', 'give'),
            trashBody: __('Are you sure you want add to trash the following subscriptions?', 'give'),
            deleteTitle: __('Delete subscriptions', 'give'),
            deleteBody: __('Really delete the following subscriptions?', 'give'),
        },
    }),
    setStatusRowAction({
        api: subscriptionsApi,
        message: <p>{__('Set status for the following subscriptions?', 'give')}</p>,
        statuses: SUBSCRIPTION_STATUSES,
        isVisible: (parameters) => !isTrashView(parameters),
    }),
];
