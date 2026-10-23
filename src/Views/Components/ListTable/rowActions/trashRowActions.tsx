import {__} from '@wordpress/i18n';
import type ListTableApi from '../api';
import type {ListTableItem, ListTableParameters, ListTableRowAction} from '../ListTablePage/types';
import {idsOf} from '../adapters/idsOf';

/**
 * @since TBD
 */
export type TrashRowActionsArgs = {
    api: ListTableApi;
    isTrashView: (parameters: ListTableParameters) => boolean;
    editHref: (item: ListTableItem) => string;
    /** Full sentences per record type, so translators get whole strings. */
    text: {
        restoreTitle: string;
        restoreBody: string;
        trashTitle: string;
        trashBody: string;
        deleteTitle: string;
        deleteBody: string;
    };
};

/**
 * The actions of a list with a trash view, through the list endpoint's `/untrash`, `/trash` and
 * `/delete` actions: "Edit" inline (or "Restore" in the trash view), "Trash" (or "Delete
 * permanently" in the trash view) in the row menu. All but "Edit" also run on a selection.
 *
 * @since TBD
 */
export function trashRowActions({api, isTrashView, editHref, text}: TrashRowActionsArgs): ListTableRowAction[] {
    const inTrash = (data, parameters: ListTableParameters) => isTrashView(parameters);
    const outsideTrash = (data, parameters: ListTableParameters) => !isTrashView(parameters);

    return [
        {
            id: 'edit',
            label: __('Edit', 'give'),
            isPrimary: true,
            isVisible: outsideTrash,
            href: editHref,
        },
        {
            id: 'restore',
            label: __('Restore', 'give'),
            isPrimary: true,
            supportsBulk: true,
            isVisible: inTrash,
            confirm: {
                title: text.restoreTitle,
                body: () => <p>{text.restoreBody}</p>,
                confirmLabel: __('Restore', 'give'),
            },
            run: (items) => api.fetchWithArgs('/untrash', {ids: idsOf(items)}, 'POST'),
        },
        {
            id: 'trash',
            label: __('Trash', 'give'),
            supportsBulk: true,
            isVisible: outsideTrash,
            confirm: {
                title: text.trashTitle,
                body: () => <p>{text.trashBody}</p>,
                confirmLabel: __('Move to trash', 'give'),
            },
            run: (items) => api.fetchWithArgs('/trash', {ids: idsOf(items)}, 'DELETE'),
        },
        {
            id: 'delete',
            label: __('Delete permanently', 'give'),
            isDestructive: true,
            supportsBulk: true,
            isVisible: inTrash,
            confirm: {
                title: text.deleteTitle,
                body: () => <p>{text.deleteBody}</p>,
                confirmLabel: __('Delete', 'give'),
            },
            run: (items) => api.fetchWithArgs('/delete', {ids: idsOf(items)}, 'DELETE'),
        },
    ];
}
