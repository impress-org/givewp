import {__} from '@wordpress/i18n';
import type ListTableApi from '@givewp/components/ListTable/api';
import {hasStatus} from '@givewp/components/ListTable/adapters/hasStatus';
import {trashRowActions} from '@givewp/components/ListTable/rowActions/trashRowActions';
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
    return trashRowActions({
        api,
        isTrashView: isDonationsTrashView,
        editHref: (item) => `${adminUrl}edit.php?post_type=give_forms&page=give-payment-history&id=${item.id}`,
        text: {
            restoreTitle: __('Restore donations', 'give'),
            restoreBody: __('Are you sure you want remove from trash the following donations?', 'give'),
            trashTitle: __('Trash donations', 'give'),
            trashBody: __('Are you sure you want add to trash the following donations?', 'give'),
            deleteTitle: __('Delete donations', 'give'),
            deleteBody: __('Really delete the following donations?', 'give'),
        },
    });
}
