import {__} from '@wordpress/i18n';
import type ListTableApi from '@givewp/components/ListTable/api';
import {hasStatus} from '@givewp/components/ListTable/adapters/hasStatus';
import {idsOf} from '@givewp/components/ListTable/adapters/idsOf';
import type {ListTableParameters, ListTableRowAction} from '@givewp/components/ListTable/ListTablePage';

/**
 * @since TBD
 */
export const isDonationsTrashView = (parameters: ListTableParameters) => hasStatus(parameters, 'trash');

/**
 * The donation actions shared by the Donations screen and the subscription details "Donations" tab:
 * "Edit" inline ("Restore" in the trash view), "Trash" (or "Delete" in the trash view) in the row
 * menu. All but "Edit" also run on a selection.
 *
 * @since TBD Declare the actions as data for the list engine, for rows and selections.
 * @since 4.12.0 Revert delete action to use Donation Actions API and add trash and restore actions.
 * @since 4.6.0 Soft delete donations with Donation v3 API.
 */
export function donationRowActions(api: ListTableApi, adminUrl: string): ListTableRowAction[] {
    return [
        {
            id: 'edit',
            label: __('Edit', 'give'),
            isPrimary: true,
            isVisible: (data, parameters) => !isDonationsTrashView(parameters),
            href: (item) => `${adminUrl}edit.php?post_type=give_forms&page=give-payment-history&id=${item.id}`,
        },
        {
            id: 'restore',
            label: __('Restore', 'give'),
            isPrimary: true,
            supportsBulk: true,
            isVisible: (data, parameters) => isDonationsTrashView(parameters),
            confirm: {
                title: __('Restore donations', 'give'),
                body: () => <p>{__('Are you sure you want remove from trash the following donations?', 'give')}</p>,
                confirmLabel: __('Restore', 'give'),
            },
            run: (items) => api.fetchWithArgs('/untrash', {ids: idsOf(items)}, 'POST'),
        },
        {
            id: 'trash',
            label: __('Trash', 'give'),
            supportsBulk: true,
            isVisible: (data, parameters) => !isDonationsTrashView(parameters),
            confirm: {
                title: __('Trash donations', 'give'),
                body: () => <p>{__('Are you sure you want add to trash the following donations?', 'give')}</p>,
                confirmLabel: __('Move to trash', 'give'),
            },
            run: (items) => api.fetchWithArgs('/trash', {ids: idsOf(items)}, 'DELETE'),
        },
        {
            id: 'delete',
            label: __('Delete permanently', 'give'),
            isDestructive: true,
            supportsBulk: true,
            isVisible: (data, parameters) => isDonationsTrashView(parameters),
            confirm: {
                title: __('Delete donations', 'give'),
                body: () => <p>{__('Really delete the following donations?', 'give')}</p>,
                confirmLabel: __('Delete', 'give'),
            },
            run: (items) => api.fetchWithArgs('/delete', {ids: idsOf(items)}, 'DELETE'),
        },
    ];
}
